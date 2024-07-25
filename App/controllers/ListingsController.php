<?php

namespace App\Controllers;

use Framework\Database;
use Framework\Validation;

class ListingsController
{
  protected $db;

  public function __construct()
  {
    $config = require basePath('config/db.php');
    $this->db = new Database($config);
  }

  /**
   * Show all listings
   * 
   * @return void
   */

  public function index()
  {
    // inspectAndDie(Validation::string(''));
    // inspectAndDie(Validation::email('text@ext.com'));

    $listings = $this->db->query('SELECT * FROM listings')->fetchAll();

    loadView('listings/index', [
      'listings' => $listings,
    ]);
  }

  /**
   * Show thee create listing form
   * 
   * @return void
   */

  public function create()
  {
    loadView('listings/create');
  }


  /**
   * Show a single listing
   * 
   *@param array $params
   * @return void
   */

  public function show($params)
  {
    $id = $params['id'] ?? '';

    $params = [
      'id' => $id
    ];

    $listing = $this->db->query('SELECT * FROM listings WHERE id = :id', $params)->fetch();

    // Check if lisings exists
    if (!$listing) {
      ErrorController::notFound('Lising not found');
      return;
    }

    loadView('listings/show', [
      'listing' => $listing
    ]);
  }

  /**
   * Store data in databse
   * 
   * @return void
   */
  public function store()
  {
    $allowedFields = ['title', 'description', 'salary', 'tags', 'company', 'address', 'city', 'state', 'phone', 'email', 'requirements', 'benefits'];

    $newListingData = array_intersect_key($_POST, array_flip($allowedFields));

    $newListingData['user_id'] = 1;

    $newListingData = array_map('sanitize', $newListingData);

    $requiredFields = ['title', 'description', 'email', 'city', 'state', 'salary'];

    $errors = [];

    foreach ($requiredFields as $field) {
      if (empty($newListingData[$field]) || !Validation::string($newListingData[$field])) {
        $errors[$field] = ucfirst($field) . ' is requred';
      }
    }

    if (!empty($errors)) {
      //Reload view with errors
      loadView('listings/create', [
        'errors' => $errors,
        'listing' => $newListingData
      ]);
    } else {
      // Submit data
      // echo 'Success';

      $fields = [];
      foreach ($newListingData as $field => $value) {
        $fields[] = $field;
      }
      $fields = implode(', ', $fields);

      $values = [];
      foreach ($newListingData as $field => $value) {
        //  Convert empty strings to null
        if ($value === '') {
          $newListingData[$field] = null;
        }
        $values[] = ':' . $field;
      }

      $values = implode(', ', $values);

      $query = "INSERT INTO listings({$fields}) VALUES({$values})";

      $this->db->query($query, $newListingData);

      redirect('/listings');

      // inspectAndDie($values);
    }
  }

  /**
   * Delete a listing
   * 
   * @param array $params
   * @return void
   */
  public function destory($params)
  {
    $id = $params['id'];

    $params = [
      'id' => $id
    ];

    $listing = $this->db->query('SELECT*FROM listings WHERE id= :id', $params)->fetch();

    if (!$listing) {
      ErrorController::notFound('Listing not found');
      return;
    }
    // inspect($listing);

    $this->db->query('DELETE FROM listings WHERE id=:id', $params);

    //Set flash message
    $_SESSION['success_message'] = 'Listing deleted successfully';

    redirect('/listings');
  }


  /**
   * Show the listing edit form
   * 
   *@param array $params
   * @return void
   */

  public function edit($params)
  {
    $id = $params['id'] ?? '';

    $params = [
      'id' => $id
    ];

    $listing = $this->db->query('SELECT * FROM listings WHERE id = :id', $params)->fetch();

    // Check if lisings exists
    if (!$listing) {
      ErrorController::notFound('Lising not found');
      return;
    }

    // inspectAndDie($listing);

    loadView('listings/edit', [
      'listing' => $listing
    ]);
  }
}
