<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function aio_render_shortcode_generator_page() {
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Shortcode-Generator', 'aorp' ); ?></h1>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row"><label for="aio_sc_type"><?php esc_html_e( 'Shortcode', 'aorp' ); ?></label></th>
                <td>
                    <select id="aio_sc_type">
                        <option value="speisekarte">[speisekarte]</option>
                        <option value="getraenkekarte">[getraenkekarte]</option>
                        <option value="inhaltsstoffe">[inhaltsstoffe]</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="aio_sc_cols"><?php esc_html_e( 'Spalten', 'aorp' ); ?></label></th>
                <td><input type="text" id="aio_sc_cols" placeholder="2" /></td>
            </tr>
            <tr>
                <th scope="row"><label for="aio_sc_cats"><?php esc_html_e( 'Kategorien', 'aorp' ); ?></label></th>
                <td><input type="text" id="aio_sc_cats" placeholder="1,2" /></td>
            </tr>
        </table>
        <p><input type="text" id="aio_generated_sc" readonly style="width:100%" /></p>
        <p><button class="button" id="aio_copy_sc"><?php esc_html_e( 'Copy to Clipboard', 'aorp' ); ?></button></p>
    </div>
    <script>
    jQuery(function($){
        function build(){
            var type = $('#aio_sc_type').val();
            var shortcode = '['+type;
            var cols = $('#aio_sc_cols').val();
            var cats = $('#aio_sc_cats').val();
            if(cols){ shortcode += ' columns="'+cols+'"'; }
            if(cats){ shortcode += ' categories="'+cats+'"'; }
            shortcode += ']';
            $('#aio_generated_sc').val(shortcode);
        }
        $('#aio_sc_type, #aio_sc_cols, #aio_sc_cats').on('input change', build);
        $('#aio_copy_sc').on('click', function(e){
            e.preventDefault();
            $('#aio_generated_sc').select();
            document.execCommand('copy');
        });
        build();
    });
    </script>
    <?php
}
