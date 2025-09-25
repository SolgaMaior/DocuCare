<?php include('view/header.php'); ?>

<section id="list" class="list">
    <h2>Citizens</h2>
    <table>
        <tr>
            <th>Name</th>
            <th>Purok</th>
        </tr>
        <?php foreach ($citizennames as $citizen) : ?>
            <tr>
                <td><?php echo $citizen['lastname'] . ', ' . $citizen['firstname'] . ' ' . $citizen['middlename']; ?></td>
                <td><?php echo $citizen['purokID']; ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

</section>