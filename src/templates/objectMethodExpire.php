
/**
 * This item is expiring.
 *
 * @return void
 */
protected function expire()
{
    if ($this->preExpire()) {
        $this->doExpire();

        $this->postExpire();
    }
}

/**
 * Actions to be taken when the item has expired.
 *
 * @return void
 */
protected function doExpire()
{
<?php if ($auto_delete): ?>
    $this->delete();
<?php endif; ?>
}
