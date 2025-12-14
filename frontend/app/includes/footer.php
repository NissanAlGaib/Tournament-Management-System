<?php
/**
 * Common Footer Include
 * Closes body and html tags if they were opened
 */

// Only output the closing tags if head was included
if (defined('HEAD_INCLUDED')): ?>
</body>
</html>
<?php endif; ?>
