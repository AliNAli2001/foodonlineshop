**Order Status Flow (Based on Database Schema)**

This document describes the full order status flow using the exact status names defined in the database schema and clarifies the difference between client-created orders and admin-created orders.

---

## 1. Order Creation

### A. Client-Created Order

* When a user creates an order, its initial status is **pending**.
* At this stage:

  * The required product quantities are **reserved** in inventory.
  * No actual stock deduction happens yet.

### B. Admin-Created Order

* When an admin creates an order, it **starts directly with the status `confirmed`**.
* At creation time:

  * Product quantities are **immediately deducted** from inventory.
  * No reservation phase is involved.

---

## 2. Pending Order Review (Client Orders Only)

While the order is in **pending** status, the admin reviews it and chooses one of the following actions:

### A. Reject the Order

* Order status becomes **canceled**.
* All reserved quantities are **released back to inventory**.

### B. Accept the Order

* Order status becomes **confirmed**.
* Reserved quantities are **deducted from inventory** permanently.

---

## 3. Flow After Order Is Confirmed

Once an order reaches the **confirmed** status (either from admin creation or admin approval), the next status transitions depend on the **order source** and **delivery method**.

---

## 4. Orders Inside the City (`order_source = inside_city`)

### A. Delivery Method: Hand Delivered (`hand_delivered`)

* The order can transition to:

  * **done**: Order completed successfully.
  * **canceled**: Order canceled and products are **returned to inventory**.

### B. Delivery Method: Delivery (`delivery`)

* The order is assigned to a delivery person.
* The order transitions to **delivered** after delivery.

#### From `delivered`

* The order can transition to:

  * **done**: Delivery completed successfully.
  * **returned**: Order returned and products are **restored to inventory**.

* At any point before completion, the order may transition to:

  * **canceled**, with products **returned to inventory**.

---

## 5. Orders Outside the City (`order_source = outside_city`)

* From **confirmed**, the order can transition to:

  * **shipped**: Order has been shipped.
  * **canceled**: Order canceled and products are **returned to inventory**.

### From `shipped`

* The order can transition to:

  * **done**: Order completed successfully.
  * **returned**: Order returned and products are **restored to inventory**.

---

## 6. Inventory Rules Summary

* **pending**: stock is reserved only.
* **confirmed**: stock is deducted.
* **canceled**: deducted or reserved stock is returned.
* **returned**: deducted stock is returned.
* **done**: final successful state, no inventory changes.

---

## 7. Final States

The following statuses represent terminal or near-terminal states:

* **done**
* **canceled**
* **returned**

Each ensures inventory consistency according to the rules above.

---

This flow ensures clear separation between reservation, deduction, cancellation, and return logic while fully aligning with the defined database schema.
