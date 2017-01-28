<?php if(!defined( 'ABSPATH')) exit; ?>
<div class="cf7bot">
    <h2> Conversational Form Settings</h2>
    <fieldset>
        <table class="form-table">
            <tbody>
            <tr>
                <th scope="row">Activate</th>
                <td>
                    <input type="checkbox" name="cf7bot_enabled" id="cf7bot_enabled" value="1" {enabled}>
                    <label for="cf7bot_enabled">Enable</label>
                    <p class="description">Activate Form Integration</p>
                </td>
            </tr>
            <tr>
                <th scope="row">Toggle Button</th>
                <td>
                    <input type="text" name="cf7bot_toggle" class="large-text code" value="{toggle}" placeholder="e.g. #toggle">
                    <p class="description">Toggle Button element (jQuery) Selector</b></p>
                </td>
            </tr>
            <tr>
                <th scope="row">Form ID (CSS)</th>
                <td>
                    <input type="text" name="cf7bot_form_id" class="large-text code" value="{form_id}" placeholder="e.g. conversational">
                    <p class="description">Form HTML ID (without #)</b></p>
                </td>
            </tr>
            <tr>
                <th scope="row">Conversational Form Holder</th>
                <td>
                    <input type="text" name="cf7bot_form_outer" class="large-text code" value="{form_outer}" placeholder="e.g. form-outer">
                    <p class="description">DIV Container ID</b></p>
                </td>
            </tr>
            <tr>
                <th scope="row">Form Fields <b>(Required)</b></th>
                <td class="valign-top">
                    <div class="cf7bot_form_field_names_wrap">
                        <span class="cf7bot_form_fields"></span>
                        {form_fields_html}
                    </div>
                    <p class="cf7_field_names"></p>

                </td>
            </tr>

            </tbody>
        </table>
    </fieldset>
</div>
