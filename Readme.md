# ShortCode

Implementation of Wordpress' Shortcode syntax in Thelia (with https://github.com/maiorano84/shortcodes).  
This module scan the Thelia response at the research of registred short code (in DB),
 if a short code is find the module dispatch the associated event. 

## Installation

### Manually

* Copy the module into ```<thelia_root>/local/modules/``` directory and be sure that the name of the module is ShortCode.
* Activate it in your thelia administration panel

### Composer

Add it in your main thelia composer.json file

```
composer require thelia/shortcode-module:~1.0
```

## Usage

This module https://github.com/thelia-modules/ShortCodeMeta is a good example of how to use ShortCode.

### 1. Register your short codes 
 You can do this at the post activation of your module. The best way to do this is to use the module method :  `ShortCode::createNewShortCodeIfNotExist`
 the first parameter is the name you will use to call the short code in your templates (like this `[my_shortcode_name], second parameter is the event dispatched when the short code is detected.
 
### 2. Add short codes to your templates  
 The short codes syntax is as follows: 
 ```
 [shortcode] - No content, no attributes   
 [shortcode]My Content[/shortcode] - Short code with content  
 [shortcode attribute=value foo=bar] - Short code with attributes  
 [shortcode attribute=value foo=bar]My Content[/shortcode] - Short code with content and attributes  
 ``` 
 
### 3. Listen events associated to your short codes
 When a short code is detected in response a `ShortCodeEvent` is dispatched with the event name given at the creation.
 So if you want replace your short code by something you have to listen this event.
 In this event you have 3 properties :
 - `content` (string) The content between your tags it will be `My Content` for the example above.
 - `attributes` (array) The array of all attributes passed to your short code `['attribute'=>'value', 'foo'=>'bar']` for the example above.
 - `result` (string) Your short code will be replaced by this value in response (equal to content by default)