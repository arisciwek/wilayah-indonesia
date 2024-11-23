

<?php
// File: src/Views/templates/settings/tabs/permission-tab.php
?>
<div class="permissions-matrix">
    <table class="widefat fixed" id="permissions-table">
        <thead>
            <tr>
                <th>Capability</th>
                <th>Administrator</th>
                <th>Editor</th>
                <th>Author</th>
                <th>Contributor</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($capabilities as $cap => $desc): ?>
            <tr>
                <td><?php echo esc_html($desc); ?></td>
                <?php foreach ($roles as $role): ?>
                <td>
                    <input type="checkbox" 
                           name="wilayah_indonesia_permissions[<?php echo esc_attr($role); ?>][<?php echo esc_attr($cap); ?>]" 
                           <?php checked(true, $this->has_capability($role, $cap)); ?>>
                </td>
                <?php endforeach; ?>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>