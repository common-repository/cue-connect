<?php

// disable direct access
if (!defined('ABSPATH')) {
    exit();
}

/**
 * Cue Options Fields
 * 
 * Helper class to render inputs on options page
 *
 * @since 1.0.3
 */
class Cue_Options_Fields
{
    public static $group = 'cue_options';

    public static function textfield($args)
    {
        $options = CueOptions::get();
        $name = $args['name'];
        $value = isset($options[$name])?$options[$name]:'';
        $type = isset($args['type'])?$args['type']:'text';
?>
<p>
    <label for="cue-textfield-<?php echo $name ?>">
        <?php echo $args['label'] ?>
    </label>
    <input 
        id="cue-textfield-<?php echo $name ?>" 
        name="cue_options[<?php echo $name ?>]"
        type="<?php echo $type ?>"
        value="<?php echo $value ?>">
</p>
<?php
    }

    public static function select($id, $selected, $options)
    {
?>
<select name="<?php echo self::$group . "[{$id}]" ?>" id="cue-select-<?php $id ?>">
<?php foreach ($options as $value=>$label) : ?>
<option value="<?php echo $value ?>" <?php selected( $selected, $value, true ); ?>><?php echo $label ?></option>
<?php endforeach; ?>
</select>
<?php
    }

    public static function checkbox($id, $value)
    {
        if ($value) {
            $checked = true;
        } else {
            $checked = false;
        }
?>
    <input type="hidden" name="<?php echo self::$group . "[{$id}]" ?>" value="0">
    <input type="checkbox" 
        name="<?php echo self::$group . "[{$id}]" ?>" 
        id="cue-checkbox-<?php echo $id ?>"
        value="1"
        <?php if ($checked) echo "checked" ?>
        >
<?php
    }
}