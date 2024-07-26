<?php

namespace App\Controllers;

use Framework\Database;
use Framework\Session;
use Framework\Validation;
use Framework\Authorization;

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

    $listings = $this->db->query('SELECT * FROM listings ORDER BY created_at DESC')->fetchAll();

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

    $newListingData['user_id'] = Session::get('user')['id'];

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

      Session::setFlashMessage('success_message', 'Listing created successfully');

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

    // Check if listing exists
    if (!$listing) {
      ErrorController::notFound('Listing not found');
      return;
    }

    // Authorization
    if (!Authorization::isOwner($listing->user_id)) {
      // $_SESSION['error_message'] = 'You are not authorized to delete this listing';
      Session::setFlashMessage('error_message', 'You are not authorized to delete this listing');
      return redirect('/listings/' . $listing->id);
    }
    // inspect($listing);

    $this->db->query('DELETE FROM listings WHERE id=:id', $params);

    //Set flash message
    // $_SESSION['success_message'] = 'Listing deleted successfully';
    Session::setFlashMessage('success_message', 'Listing deleted successfully');

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

    // Authorization
    if (!Authorization::isOwner($listing->user_id)) {
      // $_SESSION['error_message'] = 'You are not authorized to delete this listing';
      Session::setFlashMessage('error_message', 'You are not authorized to update this listing');
      return redirect('/listings/' . $listing->id);
    }

    // inspectAndDie($listing);

    loadView('listings/edit', [
      'listing' => $listing
    ]);
  }

  /**
   * Update a listing
   * 
   * @param array $params
   * @return void
   */
  public function update($params)
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

    // Authorization
    if (!Authorization::isOwner($listing->user_id)) {
      // $_SESSION['error_message'] = 'You are not authorized to delete this listing';
      Session::setFlashMessage('error_message', 'You are not authorized to update this listing');
      return redirect('/listings/' . $listing->id);
    }

    $allowedFields = ['title', 'description', 'salary', 'tags', 'company', 'address', 'city', 'state', 'phone', 'email', 'requirements', 'benefits'];

    $updateValues = [];

    $updateValues = array_intersect_key($_POST, array_flip($allowedFields));

    // inspectAndDie($updateValues);

    $updateValues = array_map('sanitize', $updateValues);

    $requiredFields = ['title', 'description', 'salary', 'email', 'city', 'state'];

    $errors = [];

    foreach ($requiredFields as $field) {
      if (empty($updateValues[$field]) || !Validation::string($updateValues[$field])) {
        $errors[$field] = ucfirst($field) . ' is required';
      }
    }

    if (!empty($errors)) {
      loadView('listings/edit', [
        'listing' => $listing,
        'errors' => $errors
      ]);
      exit;
    } else {
      //Submit to database
      // inspectAndDie('Success');
      $updateFields = [];

      foreach (array_keys($updateValues) as $field) {
        $updateFields[] = "{$field} = :{$field}";
      }

      $updateFields = implode(', ', $updateFields);

      $updateQuery = "UPDATE listings SET $updateFields WHERE id = :id";

      $updateValues['id'] = $id;
      $this->db->query($updateQuery, $updateValues);

      Session::setFlashMessage('success_message', 'Listing Updated');

      redirect('/listings/' . $id);
    }

    // inspectAndDie($errors);
  }

  /**
   * Search lisitngs by keywords/location
   * 
   * @return void
   */
  public function search()
  {
    // inspectAndDie($_GET);
    $keywords = isset($_GET['keywords']) ? trim($_GET['keywords']) : '';
    $location = isset($_GET['location']) ? trim($_GET['location']) : '';

    $query = "SELECT * FROM listings WHERE (title LIKE :keywords OR description LIKE :keywords OR tags LIKE :keywords OR company LIKE :keywords) AND (city LIKE :location OR state LIKE :location)";

    $params = [
      'keywords' => "%{$keywords}%",
      'location' => "%{$location}%"
    ];

    $listings = $this->db->query($query, $params)->fetchAll();

    // inspectAndDie($listings);

    loadView('/listings/index', [
      'listings' => $listings,
      'keywords' => $keywords,
      'location' => $location
    ]);
  }
}
