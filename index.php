<?php 

    require __DIR__ . '/db.class.php';

    $db = new DB('localhost', 'blog', 'root', '');

    /* $db -> insert('users', array(
        'user_name' => 'emre',
        'user_password' => md5('deneme')
    )) -> run();

    */

    // $db -> update('users', ['user_name' => 'is_changed']) -> where('user_id', 5) -> run();

    // $db -> delete('users') -> where('user_id', 6) -> run();

    // $users = $db -> from('users') -> getAll();

    // $db -> select('user2.id, user.id') -> from('users') -> join('user2', 'user.id = user2.id') -> where('user_id', 0, '>') -> or ('user_name', 'emre') -> getAll();

    // print_r($db -> getStmts());

    $users = $db -> select('user_id') ->from('users') -> join('countries', 'users.user_country=countries.country_id') -> getAll();

    print_r($users);

    // print_r($users);
 
?>