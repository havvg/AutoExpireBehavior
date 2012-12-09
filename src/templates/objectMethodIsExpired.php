
/**
 * Check whether this item is expired.
 *
 * @return bool
 */
public function isExpired()
{
<?php if (!$required): ?>
    if (!$this-><?php echo $getExpiresAt ?>(null)) {
        return false;
    }

<?php endif; ?>
    $now = new DateTime();

    return ($now > $this-><?php echo $getExpiresAt ?>(null));
}
