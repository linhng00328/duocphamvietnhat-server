const express = require("express");
const bodyParser = require("body-parser");
const mysql = require("mysql");
const NodeCache = require("node-cache");
const cache = new NodeCache();

const connection = mysql.createConnection({
    host: "116.118.50.101",
    user: "doshop_server_db",
    password: "12366552261236655226",
    database: "doshop_server_db",
});

connection.connect((err) => {
    if (err) {
        console.error("Error connecting to MySQL database: " + err.stack);
        return;
    }
    console.log("Connected to MySQL database as ID " + connection.threadId);
});

const app = express();
const port = 9999;
app.use(bodyParser.json({ limit: "50mb" }));
app.use(bodyParser.urlencoded({ limit: "50mb", extended: true }));

// var listIdProduct = [];
// var line_items_in_time = [];
var used_discount = [];
var user = null;
var staff = null;
var customer = null;
var is_order_for_customer = null;
var collaborator_by_customer_id;
var pointSetting;

function queryAsync(sql, values) {
    return new Promise((resolve, reject) => {
        connection.query(sql, values, (error, results, fields) => {
            if (error) {
                reject(error);
            } else {
                resolve(results);
            }
        });
    });
}

function check_type_distribute(productExists) {
    let type_product = "NO_ELE_SUB";

    if (
        productExists.distributes != null &&
        productExists.distributes.length > 0
    ) {
        if (
            productExists.distributes[0].element_distributes &&
            productExists.distributes[0].element_distributes.length > 0
        ) {
            if (
                productExists.distributes[0].element_distributes[0]
                    .sub_element_distributes.length > 0
            ) {
                type_product = "HAS_SUB";
            } else {
                type_product = "HAS_ELE";
            }
        }
    } else {
        type_product = "NO_ELE_SUB";
    }

    return type_product;
}

function isUser() {
    if (user != null || staff != null) {
        return true;
    }
    return false;
}

async function isAgency() {
    if (customer != null) {
        let agency = await getAgencyByCustomerId(customer.id);

        if (agency == null) return false;

        if (agency.status != 1) {
            return false;
        }
        return true;
    }
    return false;
}

async function isAgencyByCustomerId(customer_id) {
    let agency = await getAgencyByCustomerId(customer_id);
    if (agency == null) return false;
    if (agency.status != 1) {
        return false;
    }
    return true;
}

async function get_price_with_distribute(
    product,
    distribute_name,
    sub_distribute_name,
    column = "price",
    is_order_for_customer,
    customer,
    returnMainPriceIfNonEle = true
) {
    const price_is_ordered_initially = await get_price_agency_order_initially(
        customer,
        product
    );

    const price_product = is_order_for_customer
        ? price_is_ordered_initially
        : product[column];
    const type_product = check_type_distribute(product);

    if (distribute_name == null) {
        if (
            (type_product == "HAS_SUB" || type_product == "HAS_ELE") &&
            returnMainPriceIfNonEle == false
        ) {
            return false;
        }

        return price_product;
    }

    if (product.distributes.length == 0) {
        return price_product;
    }

    if (product.distributes[0].element_distributes.length == 0) {
        return price_product;
    }
    let final_price;
    let found = false;
    product.distributes[0].element_distributes.forEach(
        (element_distributes) => {
            if (found) return;
            if (element_distributes.name == distribute_name) {
                //kiểm tra sub của distrite đó có > 0
                if (
                    element_distributes.sub_element_distributes.length > 0 &&
                    sub_distribute_name != null
                ) {
                    element_distributes.sub_element_distributes.forEach(
                        (sub_element_distributes) => {
                            if (
                                sub_element_distributes.name ==
                                sub_distribute_name
                            ) {
                                const a =
                                    (column == "price"
                                        ? sub_element_distributes.price
                                        : sub_element_distributes.price_before_override) ??
                                    price_product;
                                final_price = a;
                                found = true;
                                return a;
                            }
                        }
                    );
                } else {
                    final_price =
                        (column == "price"
                            ? element_distributes.price
                            : element_distributes.price_before_override) ??
                        price_product;
                    found = true;
                    return (
                        (column == "price"
                            ? element_distributes.price
                            : element_distributes.price_before_override) ??
                        price_product
                    );
                }
            }
        }
    );
    if (final_price) {
        return final_price;
    }

    if (
        (type_product == "HAS_SUB" || type_product == "HAS_ELE") &&
        returnMainPriceIfNonEle == false
    ) {
        return false;
    }
    return price_product;
}

async function get_main_price_with_agency_type(
    product_id,
    agency_type_id,
    default_price
) {
    let mainPrice = null;

    if (isUser() || (await isAgency())) {
        try {
            const cacheKey = JSON.stringify([
                "product_id",
                product_id,
                "get_main_price_with_agency_type",
                agency_type_id,
            ]);
            mainPrice = cache.get(cacheKey);

            if (mainPrice === undefined) {
                let mainPrice1 = await queryAsync(
                    `
                    SELECT *
                    FROM agency_main_price_overrides
                    WHERE product_id = ?
                    AND agency_type_id = ?
                    LIMIT 1
                `,
                    [product_id, agency_type_id]
                );
                mainPrice = mainPrice1[0];

                cache.set(cacheKey, mainPrice, 6);
            }
        } catch (error) {
            console.error("Error fetching main price:", error);
            throw error;
        }
    }
    return parseFloat(mainPrice == null ? default_price : mainPrice.price);
}

async function get_price_agency_order_initially(customer = nul, product) {
    let main_price = null;
    if (customer != null) {
        let agency = await getAgencyByCustomerId(customer.id);

        if (agency != null) {
            const a = await get_main_price_with_agency_type(
                product.id,
                agency.agency_type_id,
                product.price
            );
            return a;
        }
    }

    return parseFloat(main_price !== undefined ? main_price : product.price);
}

async function getAgencyByCustomerId(customer_id) {
    let [agency] = await queryAsync(
        `
        SELECT *
        FROM agencies
        WHERE customer_id =?
        LIMIT 1
    `,
        [customer_id]
    );
    if (agency == null) return null;

    if (agency?.status != 1) {
        return null;
    }
    return agency;
}

function auto_choose_distribute(product) {
    const data = {};
    const type_product = check_type_distribute(product);

    if (type_product == "HAS_ELE") {
        let element_distributes = product.distributes[0].element_distributes;
        element_distributes.forEach((element_distribute) => {
            data.distribute_name = product.distributes[0].name;
            data.element_distribute_id = element_distribute.id;
            data.element_distribute_name = element_distribute.name;
        });
    }

    if (type_product == "HAS_SUB") {
        let element_distributes = product.distributes[0].element_distributes;
        element_distributes.forEach((element_distribute) => {
            element_distribute.sub_element_distributes.forEach(
                (sub_element_distribute) => {
                    data.distribute_name = product.distributes[0].name;
                    data.element_distribute_name = element_distribute.name;
                    data.element_distribute_id = element_distribute.id;
                    data.sub_element_distribute_id = sub_element_distribute.id;
                    data.sub_element_distribute_name =
                        sub_element_distribute.name;
                }
            );
        });
    }

    return data;
}

async function get_percent_agency_with_agency_type(product_id, agency_type_id) {
    let [mainPrice] = await queryAsync(
        `
        SELECT *
        FROM agency_main_price_overrides
        WHERE product_id = ?
        AND agency_type_id = ?
        LIMIT 1
    `,
        [product_id, agency_type_id]
    );

    return parseFloat(mainPrice == null ? 0 : mainPrice.percent_agency);
}

async function processData(lineItem, isCTVorCus, store_id, allCart) {
    try {
        let line_items_in_time = [];
        let listIdProduct = [];
        let total_before_discount = 0;
        let total_before_discount_override = 0;
        let total_commission_order_for_customer = 0;
        let point_for_agency = 0;
        let package_weight =
            (lineItem.product.weight <= 0 ? 100 : lineItem.product.weight) *
            lineItem.quantity;

        let is_use_product_retail_step =
            lineItem.product.is_use_product_retail_step && isCTVorCus;
        let priceBySteps = 0;
        let image_url = null;

        if (lineItem.product.images.length > 0) {
            image_url = lineItem.product.images[0].url;
        }

        if (is_use_product_retail_step) {
            let [product_retail_step] = await queryAsync(
                `
                SELECT *
                FROM product_retail_steps
                WHERE store_id = ?
                    AND product_id = ?
                    AND from_quantity <= ?
                ORDER BY from_quantity DESC
                LIMIT 1
            `,
                [
                    lineItem.product.store_id,
                    lineItem.product_id,
                    lineItem.quantity,
                ]
            );
            if (product_retail_step) {
                priceBySteps = product_retail_step.price;
            }

            if (!product_retail_step) {
                is_use_product_retail_step = false;
            }
        }

        lineItem.before_discount_price = lineItem.product.price;

        if (is_use_product_retail_step) {
            lineItem.before_discount_price = priceBySteps;
            lineItem.price_before_override = priceBySteps;
        }

        let type_product = check_type_distribute(lineItem.product);

        if (
            (lineItem.distributes_selected != null &&
                lineItem?.distributes_selected?.length > 0) ||
            type_product == "HAS_SUB" ||
            type_product == "HAS_ELE"
        ) {
            lineItem.before_discount_price = await get_price_with_distribute(
                lineItem.product,
                lineItem.distributes_selected[0].value ?? null,
                lineItem.distributes_selected[0].sub_element_distributes ??
                    null,
                "price",
                is_order_for_customer,
                customer,
                false
            );

            if (lineItem.before_discount_price == false) {
                let disAuto = auto_choose_distribute(lineItem.product);

                const jsonDistributes = JSON.stringify([
                    {
                        name: disAuto.distribute_name || "",
                        sub_element_distributes:
                            disAuto.sub_element_distribute_name || "",
                        value: disAuto.element_distribute_name || "",
                    },
                ]);

                await queryAsync(
                    "UPDATE ccart_items SET distributes = ? WHERE id = ?",
                    [jsonDistributes, lineItem.id]
                );

                lineItem.before_discount_price =
                    await get_price_with_distribute(
                        lineItem.product,
                        lineItem.distributes_selected[0].value ?? null,
                        lineItem.distributes_selected[0]
                            .sub_element_distributes ?? null,
                        "price",
                        is_order_for_customer,
                        customer,
                        false
                    );
            }

            lineItem.price_before_override = await get_price_with_distribute(
                lineItem.product,
                lineItem.distributes_selected[0].value ?? null,
                lineItem.distributes_selected[0].sub_element_distributes ??
                    null,
                "min_price_before_override",
                is_order_for_customer,
                customer
            );
        }

        if (lineItem.is_bonus == false) {
            total_before_discount +=
                lineItem.before_discount_price * lineItem.quantity;
            total_before_discount_override +=
                lineItem.price_before_override * lineItem.quantity;
        }

        let before_discount_price = lineItem.before_discount_price ?? 0;
        let price_before_override = lineItem.price_before_override ?? 0;
        let product_item_after_discount = 0;
        let product_item_after_discount_override = 0;

        let total_price_discount_has_edit = 0;
        let product_discount_amount = 0;
        let product_discount_amount_override = 0;

        if (lineItem.has_edit_item_price == true) {
            total_price_discount_has_edit +=
                lineItem.quantity *
                (lineItem.before_discount_price - lineItem.item_price);

            const matchingCartItem = allCart.find(
                (cartItem) => cartItem.id === lineItem.id
            );
            if (matchingCartItem) {
                matchingCartItem.item_price = lineItem.item_price;
            }

            const obj = {
                id: lineItem.product.id,
                sku: lineItem.product.sku,
                quantity: lineItem.quantity,
                name: lineItem.product.name,
                image_url: image_url,
                item_price: lineItem.item_price,
                main_price: lineItem.product.price,
                before_discount_price: lineItem.before_discount_price,
                price_before_override: lineItem.price_before_override,
                after_discount: lineItem.after_discount,
                distributes_selected: lineItem.distributes_selected,
                percent_collaborator: lineItem.product.percent_collaborator,
                type_share_collaborator_number:
                    lineItem.product.type_share_collaborator_number,
                money_amount_collaborator:
                    lineItem.product.money_amount_collaborator,
                percent_agency: lineItem.product.percent_agency ?? 0,
                is_bonus: lineItem.is_bonus,
                parent_cart_item_ids: lineItem.parent_cart_item_ids,
                note: lineItem.note,
            };
            line_items_in_time.push(obj);
        } else if (lineItem.product.product_discount != null) {
            // before_discount_price = 0;
            // price_before_override = 0;
            if (lineItem.is_bonus == false) {
                let product_item_discount_value =
                    lineItem.before_discount_price *
                    (lineItem.product.product_discount.value / 100);
                product_item_after_discount =
                    lineItem.before_discount_price *
                    (1 - lineItem.product.product_discount.value / 100);

                let product_discount_amount_step = parseInt(
                    product_item_discount_value * lineItem.quantity
                );
                product_discount_amount += product_discount_amount_step;

                let product_item_discount_value_override =
                    lineItem.price_before_override *
                    (lineItem.product.product_discount.value / 100);
                product_item_after_discount_override =
                    lineItem.price_before_override *
                    (1 - lineItem.product.product_discount.value / 100);

                let product_discount_amount_step_override = parseInt(
                    product_item_discount_value_override * lineItem.quantity
                );
                product_discount_amount_override +=
                    product_discount_amount_step_override;
            } else {
                product_item_after_discount = 0;
                product_item_after_discount_override = 0;
                before_discount_price = lineItem.before_discount_price;
                price_before_override = lineItem.price_before_override;
            }

            await queryAsync(
                `UPDATE ccart_items SET before_discount_price = ?, price_before_override = ?, item_price = ?  WHERE id = ?`,
                [
                    before_discount_price,
                    price_before_override,
                    product_item_after_discount,
                    lineItem.id,
                ]
            );
            used_discount.push({
                id: lineItem.product.id,
                quantity: lineItem.quantity,
                name: lineItem.product.name,
                image_url: "",
                item_price: product_item_after_discount,
                before_discount_price: product_item_after_discount,
                main_price: lineItem.product.price,
                before_discount_price: before_discount_price,
                before_discount_price_override: price_before_override,
                after_discount: product_item_after_discount,
                after_discount_override: product_item_after_discount_override,
                distributes_selected: lineItem.distributes_selected,
                percent_collaborator: lineItem.product.percent_collaborator,
                type_share_collaborator_number:
                    lineItem.product.type_share_collaborator_number,
                money_amount_collaborator:
                    lineItem.product.money_amount_collaborator,
                percent_agency: lineItem.product.percent_agency,
            });

            const matchingCartItem = allCart.find(
                (cartItem) => cartItem.id === lineItem.id
            );
            if (matchingCartItem) {
                matchingCartItem.item_price = product_item_after_discount;
            }

            line_items_in_time.push({
                id: lineItem.product.id,
                sku: lineItem.product.sku,
                quantity: lineItem.quantity,
                name: lineItem.product.name,
                image_url: "",
                item_price: product_item_after_discount,
                price_before_override: product_item_after_discount_override,
                main_price: lineItem.product.price,
                before_discount_price: before_discount_price,
                before_discount_price_override: price_before_override,
                after_discount: product_item_after_discount,
                after_discount_override: product_item_after_discount_override,
                distributes_selected: lineItem.distributes_selected,
                percent_collaborator: lineItem.product.percent_collaborator,
                type_share_collaborator_number:
                    lineItem.product.type_share_collaborator_number,
                money_amount_collaborator:
                    lineItem.product.money_amount_collaborator,
                percent_agency: lineItem.product.percent_agency,
                is_bonus: lineItem.is_bonus,
                parent_cart_item_ids: lineItem.parent_cart_item_ids,
                note: lineItem.note,
            });
        } else {
            if (lineItem.is_bonus == false) {
                await queryAsync(
                    `UPDATE ccart_items SET before_discount_price = ?, price_before_override = ?, item_price = ?  WHERE id = ?`,
                    [
                        before_discount_price,
                        price_before_override,
                        before_discount_price,
                        lineItem.id,
                    ]
                );
            } else {
                await queryAsync(
                    `UPDATE ccart_items SET before_discount_price = ?, price_before_override = ?, item_price = ?  WHERE id = ?`,
                    [
                        before_discount_price,
                        price_before_override,
                        0,
                        lineItem.id,
                    ]
                );
            }

            const matchingCartItem = allCart.find(
                (cartItem) => cartItem.id === lineItem.id
            );
            if (matchingCartItem) {
                matchingCartItem.item_price = before_discount_price;
            }

            line_items_in_time.push({
                id: lineItem.product.id,
                sku: lineItem.product.sku,
                quantity: lineItem.quantity,
                name: lineItem.product.name,
                image_url: "",
                item_price: before_discount_price,
                price_before_override: price_before_override,
                main_price: lineItem.product.price,
                before_discount_price: before_discount_price,
                before_discount_price_override: price_before_override,
                after_discount: lineItem.item_price,
                after_discount_override: price_before_override,
                distributes_selected: lineItem.distributes_selected,
                percent_collaborator: lineItem.product.percent_collaborator,
                type_share_collaborator_number:
                    lineItem.product.type_share_collaborator_number,
                money_amount_collaborator:
                    lineItem.product.money_amount_collaborator,
                percent_agency: lineItem.product.percent_agency,
                is_bonus: lineItem.is_bonus,
                parent_cart_item_ids: lineItem.parent_cart_item_ids,
                note: lineItem.note,
            });
        }
        let share_collaborator = 0;
        let share_agency = 0;
        if (
            lineItem.is_bonus == false &&
            lineItem.product.percent_collaborator !== null &&
            lineItem.product.type_share_collaborator_number == 0 &&
            lineItem.product.percent_collaborator > 0 &&
            lineItem.product.percent_collaborator < 100
        ) {
            share_collaborator +=
                lineItem.item_price *
                (lineItem.product.percent_collaborator / 100) *
                lineItem.quantity;
        } else if (
            lineItem.is_bonus == false &&
            lineItem.product.money_amount_collaborator >= 0 &&
            lineItem.product.type_share_collaborator_number == 1
        ) {
            share_collaborator =
                share_collaborator +
                lineItem.product.money_amount_collaborator * lineItem.quantity;
        }

        let agency_customer_id = (await isAgencyByCustomerId(
            collaborator_by_customer_id
        ))
            ? collaborator_by_customer_id
            : null;

        if (
            lineItem.is_bonus == false &&
            agency_customer_id != null &&
            (customer == null || customer.id != agency_customer_id)
        ) {
            let agency = await getAgencyByCustomerId(
                collaborator_by_customer_id
            );
            if (agency != null) {
                let percent_agency = await get_percent_agency_with_agency_type(
                    lineItem.product.id,
                    agency.agency_type_id
                );
                share_agency =
                    share_agency +
                    lineItem.item_price *
                        (percent_agency / 100) *
                        lineItem.quantity;
            }
        }

        if (
            lineItem.is_bonus == false &&
            is_order_for_customer == true &&
            customer != null
        ) {
            let agency = await getAgencyByCustomerId(customer.id);
            if (agency != null) {
                let percent_agency = await get_percent_agency_with_agency_type(
                    lineItem.product.id,
                    agency.agency_type_id
                );
                total_commission_order_for_customer =
                    total_commission_order_for_customer +
                    lineItem.item_price *
                        (percent_agency / 100) *
                        lineItem.quantity;
                share_agency = total_commission_order_for_customer;
                share_collaborator = 0;
            }
        }

        if (pointSetting != null) {
            if (pointSetting.bonus_point_product_to_agency == true) {
                if (lineItem.is_bonus == false) {
                    point_for_agency =
                        point_for_agency +
                        lineItem.product.point_for_agency * lineItem.quantity;
                }
                if (
                    lineItem.is_bonus == true &&
                    pointSetting != null &&
                    pointSetting.bonus_point_bonus_product_to_agency == true
                ) {
                    point_for_agency =
                        point_for_agency +
                        lineItem.product.point_for_agency * lineItem.quantity;
                }
            }
        }

        // let listIdProduct = [];

        let index = listIdProduct.findIndex(
            (item) => item.id === lineItem.product.id
        );

        if (index !== -1) {
            let existingItem = listIdProduct[index];

            let after_quantity = existingItem.quantity;
            let new_quantity = lineItem.quantity;

            let after_price = existingItem.price_or_discount;
            let new_price = lineItem.item_price;

            let avg_price = (after_price + new_price) / 2;
            let total_quantity = after_quantity + new_quantity;

            listIdProduct[index] = {
                id: lineItem.product.id,
                quantity: total_quantity,
                price_or_discount: avg_price,
                is_bonus: lineItem.is_bonus,
            };
        } else {
            listIdProduct.push({
                id: lineItem.product.id,
                quantity: lineItem.quantity,
                price_or_discount: lineItem.item_price,
                is_bonus: lineItem.is_bonus,
            });
        }

        return {
            line_items_in_time,
            listIdProduct,
            share_agency,
            share_collaborator,
            package_weight,
            total_before_discount,
            total_before_discount_override,
            product_discount_amount,
            product_discount_amount_override,
            total_commission_order_for_customer,
            point_for_agency,
            total_price_discount_has_edit,
        };
    } catch (error) {
        throw error;
    }
}

app.post("/api/data", async (req, res) => {
    const allCart = req.body.allCart;
    const isCTVorCus = req.body.isCTVorCus;
    const store_id = req.body.store_id;
    user = req.body.user || null;
    staff = req.body.staff || null;
    customer = req.body.customer || null;
    is_order_for_customer = req.body.is_order_for_customer;
    // let total_before_discount_override =
    //     req.body.total_before_discount_override;
    // let total_before_discount = req.body.total_before_discount;
    // let product_discount_amount_override =
    //     req.body.product_discount_amount_override;
    used_discount = req.body.used_discount;
    // let share_collaborator = req.body.share_collaborator;
    collaborator_by_customer_id = req.body.collaborator_by_customer_id;
    // let share_agency = req.body.share_agency;
    // let total_commission_order_for_customer =
    //     req.body.total_commission_order_for_customer;
    pointSetting = req.body.pointSetting;
    // let point_for_agency = req.body.point_for_agency;

    try {
        // const [userData, countData] = await Promise.all([
        //     queryAsync("SELECT * FROM orders"),
        //     queryAsync("SELECT COUNT(*) as count FROM users"),
        // ]);

        // return res.json({
        //     users: userData,
        //     count: countData[0].count,
        // });

        const processedResults = await Promise.all(
            allCart.map((lineItem) =>
                processData(lineItem, isCTVorCus, store_id, allCart)
            )
        );
        let package_weight = 0;
        let share_agency = 0;
        let share_collaborator = 0;
        let total_before_discount = 0;
        let total_before_discount_override = 0;
        let product_discount_amount = 0;
        let product_discount_amount_override = 0;
        let total_commission_order_for_customer = 0;
        let point_for_agency = 0;
        let total_price_discount_has_edit = 0;
        let listIdProduct = [];
        let line_items_in_time = [];
        processedResults.forEach((item) => {
            package_weight += item.package_weight;
            share_agency += item.share_agency;
            share_collaborator += item.share_collaborator;
            total_before_discount += item.total_before_discount;
            total_before_discount_override +=
                item.total_before_discount_override;
            product_discount_amount += item.product_discount_amount;
            product_discount_amount_override +=
                item.product_discount_amount_override;
            total_commission_order_for_customer +=
                item.total_commission_order_for_customers;
            point_for_agency += item.point_for_agency;
            total_price_discount_has_edit += item.total_price_discount_has_edit;
            line_items_in_time.push(...item.line_items_in_time);
            listIdProduct.push(...item.listIdProduct);
        });

        // console.log("listIdProduct", listIdProduct);
        // console.log("line_items_in_time", line_items_in_time);

        return res.json({
            // total_before_discount,
            // total_before_discount_override,
            // product_discount_amount_override,
            // used_discount,
            // share_collaborator,
            // share_agency,
            // total_commission_order_for_customer,
            // point_for_agency,
            // line_items_in_time,
            // listIdProduct,
            // processedResults,
            package_weight,
            share_agency,
            share_collaborator,
            total_before_discount,
            total_before_discount_override,
            product_discount_amount,
            product_discount_amount_override,
            total_commission_order_for_customer,
            point_for_agency,
            total_price_discount_has_edit,
            used_discount,
            line_items_in_time,
            listIdProduct,
            allCart,
        });
    } catch (error) {
        console.error(error);
        return res.status(500).json({ message: "Internal server error" });
    }
});

app.listen(port, () => {
    console.log(`Node.js API listening at http://localhost:${port}`);
});
