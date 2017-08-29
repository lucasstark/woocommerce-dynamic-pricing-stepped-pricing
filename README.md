# WooCommerce Dynamic Pricing - Stepped Pricing

Welcome to the WooCommerce Dynamic Pricing - Stepped Pricing repository on GitHub. Here you can browse the source, look at open issues and keep track of development.

The stepped pricing module replaces the standard Advanced Category module.  Specifically when using the Advanced Category module to create special offer rule types.  

This plugin is not feature complete, meaning that it is designed for a very specific purpose.  

If you need to create discount blocks and still charge full price for a certian amount of items based on categories, this will work to do that. 


## Example
If you want to sell DVD's in set's of two at a discount you would configure a Special Offer rule for your DVD category.  Set the Purchase and Receive quantity to 2 and mark as repeating. 

This will result in 1 DVD being full priced, 2 DVD's receiving the adjusted amount, 3 DVD's receiving 2 at the adjusted amount and 1 at full price.  4 items would have all 4 items adjusted.  5 would have 4 items adjusted and the 5th full price. 




## Configuration
Create an Advanced Category Sepcial Offer rule as normal.  Set the Purchase amount to the amount which needs to be in the cart to receive the discount.  Set the Receive amount to the amount of those items which should have a price adjustment applied. 

Typically you will want the Purchase and Receive amounts to be the same, allowing you to set discounts in even or odd blocks. 




## Support
This repository is not suitable for support. Please don't use the issue tracker for support requests for Dynamic Pricing. 

* The [WooCommerce premium support portal](https://woocommerce.com/my-account/create-a-ticket/) for customers who have purchased themes or extensions.
* [Our community forum on wp.org](https://wordpress.org/support/plugin/woocommerce) which is available for all WooCommerce users.

Support requests for Dynamic Pricing itself will not be addressed in this repository. 
