# wpgraphql-application-passwords
** This is a beta version of application password support for WPGraphQL.  You could use this plugin to create a mutation when a user needs to authenticate themselves with a service without using your human login credentials.  This will generate a application password for you to use instead.

The flow would go something like this:

* Execute the login GraphQL mutation operation to sign a user into the application.

* The login GraphQL mutation will return a password and application ID when a user successfully signs in. We'll need to keep track of that token.

* When a user is signed in and a valid password is generated we need to add the token to all GraphQL requests as an authentication header.

* Allow a user to sign out.
