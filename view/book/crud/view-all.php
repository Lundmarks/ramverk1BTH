<?php

namespace Anax\View;

/**
 * View to display all books.
 */
// Show all incoming variables/functions
//var_dump(get_defined_functions());
//echo showEnvironment(get_defined_vars());

// Gather incoming variables and use default values if not set
$items = isset($items) ? $items : null;

// Create urls for navigation
$urlToCreate = url("book/create");
$urlToDelete = url("book/delete");



?><h1>View all items</h1>

<p>
    <a href="<?= $urlToCreate ?>">Create</a> |
    <a href="<?= $urlToDelete ?>">Delete</a>
</p>

<?php if (!$items) : ?>
    <p>There are no items to show.</p>
    <?php
    return;
endif;
?>

<table>
    <tr>
        <th>Book no.</th>
        <th>Book Title</th>
        <th>Book Author</th>
        <th>Image</th>
    </tr>
    <?php foreach ($items as $item) : ?>
    <tr>
        <td>
            <a href="<?= url("book/update/{$item->id}"); ?>"><?= $item->id ?></a>
        </td>
        <td><p style="min-width: 245px;text-align:center;"><?= $item->bookTitle ?></p></td>
        <td><p style="min-width: 245px;text-align:center;"><?= $item->bookAuthor ?></p></td>
        <td><img src="<?= $item->imageLink ?>" alt="<?= $item->bookTitle ?>" height="150" style="display:block;margin-left:auto;margin-right:auto;"></td>
    </tr>
    <?php endforeach; ?>
</table>
