=== uBind ===
Tags: ubind
Requires at least: 4.9.7
Tested up to: 6.5
Requires PHP: 5.6.36
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The uBind WordPress plugin is used to embed a uBind product form and/or portal to your WordPress page.

== Description ==
For information and detailed documentation, please visit the [uBind](https://ubind.insure/ubind-wordpress-plugin/) Plugin Page

The uBind WordPress plugin is used to embed a uBind product form and/or portal to your WordPress page. The plugin itself does two main things:
In the HTML head, it adds the following script tag:
	&lt;script async=&quot;true&quot; src=&quot;https://app.ubind.com.au/assets/ubind.js&quot; type=&quot;text/javascript&quot;&gt;&lt;/script&gt;

In the HTML body, it adds the following element in place of the WordPress shortcode:
	For a uBind product
	&lt;div class=&quot;ubind-product&quot; data-tenant=&quot;tenant-alias&quot; data-product=&quot;product-alias&quot; data-organisation=&quot;organisation-alias&quot; data-environment=&quot;environment&quot;&gt;&lt;/div&gt;
	For a uBind portal
	&lt;div class=&quot;ubind-portal&quot; id=&quot;embedded-portal&quot; data-tenant=&quot;tenant-alias&quot; data-organisation=&quot;organisation-alias&quot; data-portal=&quot;portal-alias&quot; data-environment=&quot;environment&quot;&gt;&lt;/div&gt;
	
Then, when the page loads, the uBind javascript will load, run, and find the div. It will then read the tenant alias, product alias and other configuration details then load the uBind form into that div.
Typically, the environment will be &quot;production&quot;, however possible other values are &quot;staging&quot; or &quot;development&quot;.

If you do not have your portal/product configuration details, you may wish to get in touch with [uBind support](https://jira.aptiture.com/servicedesk/customer/portal/32/).

== Installation ==
1. Install the plugin by uploading the zip file into the plugins section.
2. Activate the Plugin.

== Configuration ==
1. (optional) If the WordPress setup uses dotEnv set the following parameters:
	Define your Default uBind configuration with the following configuration fields:
	UBIND_CONFIG_TYPE = &lt;define for which configuration type, set to 0 for product and 1 for portal&gt;
	UBIND_PRODUCT = &lt;product-alias&gt;
	UBIND_ENVIRONMENT = &lt;environment&gt;
	If you have more than one uBind form to configure, you can setup the parameters as:
	UBIND_CONFIG_TYPE_1 = &lt;1st configuration type&gt;
	UBIND_PRODUCT_1 = &lt;1st product-alias&gt;
	UBIND_ENVIRONMENT_1 = &lt;1st environment&gt;
	UBIND_CONFIG_TYPE_2 = &lt;2nd configuration type&gt;
	UBIND_PRODUCT_2 = &lt;2nd product-alias&gt;
	UBIND_ENVIRONMENT_2 = &lt;2nd environment&gt;
	Other fields you can include in the configuration, suffix an underscore and number if you have more than one uBind form to configure.
	UBIND_TENANT = &lt;tenant-alias&gt;
	UBIND_ORGANISATION = &lt;organisation&gt;
	UBIND_FORM_TYPE = &lt;possible values are quote or claim&gt;
	UBIND_SHORTCODE = &lt;short-code&gt;
	UBIND_PORTAL_SHORTCODE = &lt;portal-short-code&gt;
	UBIND_SIDEBAR_OFFSET = &lt;possible values xs,50|sm,50|md,50|lg,50&gt;
	UBIND_PORTAL_FULLSCREEN = &lt;set to 1 for portal to take over entire viewport or 0 otherwise&gt;
Always suffix an incremental number beside the dotEnv entry if there are more than one. The default uBind form values can either have a suffix of zero or no suffix.

2. Go to the WordPress dashboard. At the left sidebar, click on the uBind Settings link. Select product or portal to configure. Complete the entries for each of the fields, select an environment and click on the save changes button. Associate the quote/claim and customer to an organisation by filling up the organisation field entry. Modify the sidebar offset by entering the viewport side followed by a comma then the offset size in pixels. You may place multiple sidebar offsets by separating them with pipes. e.g. xs,50|sm,50|md,50|lg,50
You can add more uBind form configurations by using the Add New button at the bottom of the screen. Added form values will have checkboxes beside each field, you may check the boxes to load the values from the default form configuration.
For the shortcode, check the box beside the shortcode entry and click save to generate a shortcode. The generated shortcode will be based on the first 4 letters each of the tenant ID, product ID and environment values. In cases of duplicate shortcodes, the generated shortcode will be suffixed with a numerical value. e.g. [tenant_prod_1].
To create a custom shortcode, uncheck the box beside the shortcode entry, then click on the Save button. In case there is a duplicate, the admin UI will give an error notice when you try to save it. Replace the duplicate custom shortcode and click save to remove the duplicate error notice.

uBind forms will be listed in the Admin UI. To edit or view a form configuration, click on the form title or hover and click on the Quick Edit link.

To delete a form configuration, mouse over the form title then click on the Delete link. You can also delete a form by using the Delete button at the bottom of each form section.

3. To display a uBind form on any page or post within your WordPress site, copy the associated shortcode from the Admin UI and paste it into your pages or posts content.
You can also create your own custom shortcode from the admin interface by unchecking the box beside the shortcode to enable the text entry box. Enter the preferred shortcode then click on the Save button. You can then use the custom shortcode to display the associated uBind form.

To create a portal embed configuration, tick on the Portal. Note that the configuration for Portal is different from that of a product.
Fill in the portal configuration details and click save when done.
Some pages do not work well with the portal, to work around this issue you can allow the uBind plugin to take over the entire viewport of the page. To do this, tick on Yes for the Full-Screen Mode option.

== Frequently Asked Questions ==
= Where can I find uBind plugin documentation? =

For help setting up and configuring uBind plugin please refer to https://ubind.insure/ubind-wordpress-plugin/

== Changelog ==
= 1.8 =
* removed material preloader from plugin embed
= 1.7.1 =
* Added option for a portal to embed a CSS that will make use of the entire viewport.
= 1.7 =
* Plugin updates to support new features from uBind Form release 9.0.
= 1.6 =
* Update CSS version causing a PHP warning.
= 1.5 =
* Update uBind embed markup to display a preloader while the ubind.js script is being loaded.
= 1.4 =
* The reference URL to ubind.js changed from https://app.ubind.io/assets/ubind.js to https://app.ubind.com.au/assets/ubind.js.
= 1.3 =
* Updated the element inserted in the HTML body to use property data-tenant.
= 1.2 =
* Added support for multiple uBind form configurations.
* Merge .env configurations with none .env configurations.
* Added support for a default configuration that can be used by other form configurations.
= 1.1 =
* Added support for .env file configuration.
== Upgrade Notice ==
= 1.2 =
1.2 is a major update. Make a full site backup, update your theme and extensions.