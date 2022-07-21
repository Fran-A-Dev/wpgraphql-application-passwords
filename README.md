# wpgraphql-application-passwords
** This is a beta version of application password support for WPGraphQL.  You could use this plugin to create a mutation when a user needs to authenticate
themselves into WordPress and get a password and a id for the application you are logging into that is gated through WordPress and GraphQL.

The flow would go something like this:

* Execute the login GraphQL mutation operation to sign a user into the application.

* The login GraphQL mutation will return a password and application ID when a user successfully signs in. We'll need to keep track of that token.

* When a user is signed in and a valid password is generated we need to add the token to all GraphQL requests as an authentication header.

* Allow a user to sign out.
