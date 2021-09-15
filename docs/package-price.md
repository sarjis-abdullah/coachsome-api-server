# Package Price
Here we describe the business logic of package price.

## TOC
1. [Business mode](#Business mode)
2. [Total Price](#Total Price)
3. [Issue](#Issue)

## Business mode


We take:
> 5% service fee from the customer

> 15% service fee from the coach

When a customers buys a package we will add a service fee of 5% on the total order which will be added to the price that the coach has already made. Meaning that if you buy a package for $100 then the total price for the customer would be $105 at check out.

When the money has been paid from the customer, we will then take the $5 fee that we added to the total from the customer. Then there is the $100 left. Here we will take a 15% service fee from the coach which means the coach will then get $85 into their Coachsome balance.

## Total Price
We should show the total price including the 5% service fee

**For example**
if the price of the coach package is 650 DKK without a service fee, but the price including service fee is 682,5 -
we should show "683 DKK" on the package. So we will not show any decimals as the system will round the total price up.


## VAT

All our service fees on the platform is including VAT.

All prices the coach set on the platform is including vat for them - so if a coach sets their settings to VAT registred, then we will send the invoice to them with the amount that is left after our service fee and then shown that there has been taken 25% from their total.

So from the example above the coach will have $85 left for payout, since the coach is VAT registred, we will send an invoice to the coach like this:

Subtotal: $ 63,75
VAT: $ 21,25

Total: $ 85
## Issue
- [COAC-594](https://tikweb.atlassian.net/browse/COAC-594)
- [COAC-1272](https://tikweb.atlassian.net/browse/COAC-1272)
