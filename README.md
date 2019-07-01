# CoreShop Wallee Payum Connector
This Bundle activates the Wallee PaymentGateway in CoreShop.
It requires the [w-vision/payum-wallee](https://github.com/w-vision/payum-wallee) repository which will be installed automatically.

## Installation

#### 1. Composer
```json
    "coreshop/payum-wallee-bundle": "^1.0"
```

#### 2. Activate
Enable the Bundle in Pimcore Extension Manager

#### 3. Setup
Go to Coreshop -> PaymentProvider and add a new Provider. Choose `wallee` from `type` and fill out the required fields.

