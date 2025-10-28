<?php

function addalert($message, $type = 'success') {
    $alertClass = $type === 'error' ? 'alert-error' : 'alert-success';
    echo "<div class='alert $alertClass'>" . htmlspecialchars($message) . "</div>";
    echo "<script>
            setTimeout(() => {
                document.querySelector('.alert-success').style.display = 'none';
            }, 3000);
        </script>";
}