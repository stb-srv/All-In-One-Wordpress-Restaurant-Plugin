<h2><?php echo __('Kontaktformular', 'aorp'); ?></h2>
<form>
    <p><input type="text" placeholder="<?php echo esc_attr__('Name', 'aorp'); ?>" required></p>
    <p><input type="email" placeholder="<?php echo esc_attr__('E-Mail', 'aorp'); ?>" required></p>
    <p><textarea placeholder="<?php echo esc_attr__('Nachricht', 'aorp'); ?>" required></textarea></p>
    <p><button type="submit"><?php echo __('Absenden', 'aorp'); ?></button></p>
</form>

