<?php
date_default_timezone_set('Africa/Tunis');
session_start();

require_once 'controllers/AvisController.php';
require_once 'controllers/GoogleAuthController.php';
require_once 'controllers/AuthController.php';
require_once 'controllers/AdminController.php';
require_once 'controllers/EmployeController.php';
require_once 'controllers/MembreController.php';
require_once 'controllers/QrCodeController.php';
require_once 'controllers/ProfilController.php';
require_once 'controllers/CalendrierController.php';
require_once 'controllers/ApiController.php';


$page = $_GET['page'] ?? 'login';

switch ($page) {
    case 'login':          AuthController::login();          break;
    case 'register':       AuthController::register();       break;
    case 'logout':         AuthController::logout();         break;
    case 'forgot_password': AuthController::forgotPassword(); break;
    case 'reset_password':  AuthController::resetPassword();  break;

    case 'admin_dashboard':    AdminController::dashboard();    break;
    case 'admin_salles':       AdminController::salles();       break;
    case 'admin_employes':     AdminController::employes();     break;
    case 'admin_reservations': AdminController::reservations(); break;

    case 'employe_reservations': EmployeController::mesReservations(); break;
    case 'employe_reserver':     EmployeController::reserver();        break;

    case 'membre_salles':        MembreController::salles();           break;
    case 'membre_reserver':      MembreController::reserver();         break;
    case 'membre_reservations':  MembreController::mesReservations();  break;

    case 'api_creneaux': ApiController::creneaux(); break;

    case 'qrcode':      QrCodeController::generer();  break;
    
    case 'profil':      ProfilController::index();    break;
    
    case 'calendrier':  CalendrierController::index(); break;
    
    case 'google_callback': GoogleAuthController::callback(); break;

    case 'avis':        AvisController::index();       break;
    case 'avis_supprimer': AvisController::supprimer(); break;

    case 'otp_verify': AuthController::otpVerify(); break;
    
    default:
        echo "<h2>Page introuvable</h2>";
        echo "<a href='index.php?page=login'>Retour</a>";
        break;
}