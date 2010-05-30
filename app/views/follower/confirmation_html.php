<h1>Request Sent!</h1>
<p>Your request to add {$person->name} as a friend has been sent.</p>

<p>If <a href="http://{$person->url}" title="{$person->name}'s 6d site">{$person->name}</a> chooses to accept your request, a notification will be sent to your site and their 6d siteadded to your address book.</p>

<h1>Connect With Others</h1>
<p>While you wait for <a href="http://{$person->url}" title="{$person->name}'s 6d site">{$person->name}</a> to respond, do you have other friends that are also using 6d? <a href="<?php echo FrontController::urlFor('addressbook');?>" title="Addressbook">Go back to your address book and continue to add them</a>.</p>