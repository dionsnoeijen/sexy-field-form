[![Build Status](https://travis-ci.org/dionsnoeijen/sexy-field-form.svg?branch=master)](https://travis-ci.org/dionsnoeijen/sexy-field-form)

# SectionField Form

This is part of the (sexy-field-bundle)[https://github.com/dionsnoeijen/sexy-field-bundle] for Symfony. It adds support for rendering out the forms for sections. It's also required by the sexy-field-api component.

Rendering an 'edit' form can be done as follows:

	{% set form = sectionForm(section, {slug:formSlug}) %}

Omit the `slug` parameter for a create form.

After that, you can refer to the (Symfony form documentation)[http://symfony.com/doc/current/forms.html]

	{{ form_start(form) }}
	<div class="row">
	    <div class="col-md-12">
	        {{ form_errors(form) }}
	    </div>
	</div>
	<div class="row">
	    <div class="col-md-10">
	        {{ form_widget(form) }}
	    </div>
	</div>
	{{ form_end(form) }}
