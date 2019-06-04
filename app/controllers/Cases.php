<?php 

class Cases extends Controller {


    public function __construct() {

        $this->caseModel = $this->model('CaseOne');
        $this->userModel = $this->model('User');

    }

    public function index() {

      $cases = $this->caseModel->getCases();

      $data =  $cases;

      $this->view('Cases/index', $data);

    }

    public function show($id = null) {

        $caseSingle = $this->caseModel->getCaseSingle($id);

        if($caseSingle == false) {
            redirect("cases/index");
        }

        $data =  $caseSingle;

        $this->view("cases/show", $data);

    }

    public function add() {

      //Check if form is POST

      if($_SERVER['REQUEST_METHOD'] == 'POST') {

          // Sanitize post data
          $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

          $data = [
              'id'                     => trim($_POST['id']),
              'title'                  => trim($_POST['title']),
              'sub_title'              => trim($_POST['sub_title']),
              'body'                   => trim($_POST['body']),
              'title_err'              => '',
              'sub_title_err'          => '',
              'body_err'               => '',
              'date_stamp'             => '',
              'user_name'              => ''
          ];

          // Validate the user
          if($data['id'] != $_SESSION['user_id']) {
            flashMessage("add_case_restricted", "You are not allowed to ADD a new CASE");
            redirect('admin/cases');
              
          } else {

            //Validate title
            if(empty($data['title'])){
              $data['title_err'] = "Please enter a title.";
            } 

            //Validate sub_title
            if(empty($data['sub_title'])){
                $data['sub_title_err'] = "Please enter a Sub title.";
              } 

            //Validate body
            if(empty($data['body'])){
                $data['body_err'] = "Please enter a text.";
            }

            //Time in seconds
            $tm =  time();

            $date_stamp = date('d/m/y', $tm);
            $data['date_stamp'] = $date_stamp;

            // Add the user name
            $user_name = $this->userModel->findUserByIdInArray($data['id']);

            $data['user_name'] = $user_name['name'];

            //Check for No errors
            if(empty($data['title_err']) && empty($data['body_err']) && empty($data['sub_title_err'])) {

                if($this->caseModel->addCase($data)) {
                    flashMessage("add_case_success", "You have ADDED a new CASE");
                    redirect('admin/cases');
                } else {
                    die("Something went wrong addin the case");
                }
            } else {
                $this->view("cases/add", $data);
            }

          }

      } else {

          // Init data
          $data = [
              'title'               => '',
              'sub_title'           => '',
              'body'                => '',
              'title_err'           => '',
              'sub_title_err'       => '',
              'body_err'            => ''

          ];
  
      $this->view("cases/add", $data);

      }
  }


  public function edit($id = null) {


    $currentCase = $this->caseModel->findCaseByIdAsArray($id);

    // Check if the UPDATE button is pressed
    // Second iteration of method 

    if ($id == "update") {

        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            if($_POST['user_id'] != $_SESSION['user_id']) {
                flashMessage('update_case_notallowed', 'You are not allowed to UPDATE this Case.');
                redirect('admin/cases');

            } /* elseif($_POST['id'] != $currentCase['id']) {
                flashMessage('update_case_by_id_notallowed', 'You are not allowed to UPDATE this Case ID.');
                redirect('admin/cases'); } */

             elseif($_POST['user_id'] == $_SESSION['user_id']) {

                // Init data
                $data = [
                    'user_id'               => trim($_POST['user_id']),
                    'id'                    => trim($_POST['id']),
                    'title'                 => trim($_POST['title']),
                    'sub_title'             => trim($_POST['sub_title']),
                    'body'                  => trim($_POST['body']),
                    'title_err'             => '',
                    'sub_title_err'         => '',
                    'body_err'              => ''
                ];

                //Validate Title
                if(empty($data['title'])){
                    $data['title_err'] = "Please enter a Case Title.";
                }

                //Validate Sub Title
                if(empty($data['sub_title'])){
                    $data['sub_title_err'] = "Please enter a Case Sub Title.";
                }

                //Validate Body
                if(empty($data['body'])){
                    $data['body_err'] = "Please enter a Case Body.";
                }

                //Check for no Errors
                if(empty($data['title_err']) && empty($data['body_err']) && empty($data['sub_title_err'])) {
                    
                    if($this->caseModel->updateCase($data)) {
                        flashMessage("update_case_successful", "You have UPDATED this Case.");
                        redirect("admin/cases");
                    } else {
                        die("Something went wrong with UPDATING the case");
                    }

                } else {
                    
                    $_SESSION['edit_error_case'] = $data;
                    redirect("cases/redirectUpdateCases");
                }

            }

        } else {

            // Empty data - if the form request is not POST
            $data = [
                'name'                  => '',
                'email'                 => '',
                'password'              => '',
                'confirm_password'      => '',
                'name_err'              => '',
                'email_err'             => '',
                'password_err'          => '',
                'confirm_password_err'  => ''
            ];
    
        $this->view("admin/index");
        
        }

    // Check if the author of the case is the same as the current logged in user trying to Edit the case
    // This piece of code will also take us to admin/cases if we do not pass a parameter to the method edit. 
    // I.e if we only write for example the url ...cases/edit then the parameter will be null and the currentCase will be nothing. 
    } elseif($currentCase['user_id'] != $_SESSION['user_id']) {
        flashMessage("case_edit_notsuccessful", "You are not allowed to EDIT this Case.");
        redirect("admin/cases");

    } elseif($currentCase['user_id'] == $_SESSION['user_id']) {

        // Actually checks if we have pushed the UPDATE button on the Edit page before
        // By checking if there are any stored errors in the edit_error SESSION variable
        // If there are any errors from the previous visit and submition of the Edit form 
        // then we are actually redirected from the redirectUpdateCase page as a 200 in order to prevent any Post-Redirect-Get issues.
        if(isset($_SESSION['edit_error_case'])) {

            $this->view("cases/edit", $_SESSION['edit_error_case']);

        } else {
        
            // Here we are sent to the Update form on the Edit page 
            // Note that this means that we are visiting the Edit page for the first time
            // from the All Cases page. Since we are visiting the first time, it also means that there 
            // cannot be any errors stored in the edit_error SESSION.

            $data =  $currentCase;

            $this->view("cases/edit", $data);
        }
    }

  }

    public function redirectUpdateCases() {

    $this->view('cases/redirectUpdateCases');
    
    }

    public function deleteCase($id = null) {

      if(!empty($id)) {

          // Double check if user is correct
          $currentCase = $this->caseModel->findCaseById($id);

          if($currentCase->user_id != $_SESSION['user_id']) {
              flashMessage('case_delete_notsuccessful', 'You are not allowed to DELETE this Case.');
              redirect('admin/cases');
          
          } elseif($_SERVER['REQUEST_METHOD'] == 'POST') {

              if($this->caseModel->deleteCase($id)) {
                  flashMessage('case_deleted', 'The Case is deleted.');
                  redirect('admin/cases');
              }

          } else {
              redirect('admin/index');
          }
      }
  }


}



?>