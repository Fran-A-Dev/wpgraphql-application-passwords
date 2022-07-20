<?php
/**
 * Plugin Name: WPGraphQL Application Password
 * Description: Application Password Support for WPGraphQL
 * Version: 0.0.1
 * Plugin Author: Francis Agulto
 * Plugin URI: https://wpgraphql.com 
 * 
 */

add_action('graphql_register_types',function(){
   
    register_graphql_object_type('ApplicationPassword', [
        'description'=> __('application password', 'wpgraphql-application-password'),
        'fields'=> [
            'applicationName'=>['type'=>'String'],
            'applicationId'=>['type'=>'ID'],
            'created'=>['type'=>'String'],
            'lastUsed'=>['type'=>'String'],
            'lastIp'=>['type'=>'String'],
            'password'=>['type'=>'String']

        ]
    ]);
    register_graphql_mutation('login',[
        'description'=> __('login and get password response', 'wpgraphql-application-password'),
        'inputFields'=>[
            'username'=> ['type'=>['non_null'=>'String']],
            'password'=> ['type'=>['non_null'=>'String']],
            'applicationName'=> ['type'=>'String'],
            'applicationId'=> ['type'=>'ID']
        ],
        'outputFields'=>[
            'applicationPassword'=>['type'=>['non_null'=>'ApplicationPassword']],
            'user'=> ['type'=>['non_null'=>'User']]
        ],
        'mutateAndGetPayload'=>function($input,$context,$info){
          
            $payload = [];
            if (empty($input['applicationName']) && empty($input['applicationID'])){
                throw new GraphQL\Error\UserError('application name or id must be provided');
            }
            $user = wp_authenticate(sanitize_user($input['username']),trim($input['password']));

            if(is_wp_error($user)){
                throw new GraphQL\Error\UserError(! empty( $user->get_error_code() ) ? $user->get_error_code() : 'invalid login');
            }
            wp_set_current_user($user->ID);
            $payload['user'] = function() use($user) {
             
                return new WPGraphQL\Model\User($user);

            };
         
            $payload['applicationPassword'] = function() use($user, $input) {
                
                if (WP_Application_Passwords::application_name_exists_for_user($user->ID,$input['applicationName'])){
                    throw new GraphQL\Error\UserError(sprintf('Password Already Exists For Application Name %s',$input['applicationName']));
                }
                
                $new_password = WP_Application_Passwords::create_new_application_password($user->ID, ['name'=>$input['applicationName']]);
                
                if (is_wp_error($new_password)) {
                    throw new GraphQL\Error\UserError('Cannot Save Application Password');
                } 
                // {
                //     "data": {},
                //     "uuid": "4efa2612-d671-456f-9818-fd8fcb1bb2d1",
                //     "app_id": "",
                //     "name": "atewpgraphqlstoke",
                //     "password": "$P$B1iMhaaGYwPRh.kSnaW9pp3AmPCFmI0",
                //     "created": 1658268716,
                //     "last_used": null,
                //     "last_ip": null
                //   }
                return [
                    'applicationId'=>$new_password[1]['uuid'],
                    'applicationName'=>$new_password[1]['name'],
                    'created'=> $new_password[1]['created'],
                    'lastUsed'=>$new_password[1]['last_used'],
                    'lastIp'=>$new_password[1]['last_ip'],
                    'password'=>$new_password[0],
                ];
            }; 

            return $payload;
        }
    ]);
});

