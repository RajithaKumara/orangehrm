## **User Guide**

<details>
<summary>
<h3 style="cursor: pointer">> Frequent errors while trying to authorize</h3>
</summary>

### Generic

#### Trying to authorize with invalid or unsupported grant type

```json
{
    "error": "unsupported_grant_type",
    "error_description": "Grant type \"authorization_code\" not supported"
}
```

Supported grant types: [`client_credentials`](https://tools.ietf.org/html/rfc6749#section-4.4), [`password`](https://tools.ietf.org/html/rfc6749#section-4.3), [`refresh_token`](https://tools.ietf.org/html/rfc6749#section-6)

&nbsp;

#### Trying to authorize with invalid scope

```json
{
    "error": "invalid_scope",
    "error_description": "An unsupported scope was requested"
}
```

&nbsp;

#### Trying to authorize using unauthorized grant type

If a client restricted for some grant types, and request grant for the restricted grant, then this happen.

e.g. A client created by only allowed to `password` grant type, but request access token for `client_credentials` grant type

```json
{
    "error": "unauthorized_client",
    "error_description": "The grant type is unauthorized for this client_id"
}
```

&nbsp;

#### Trying to authorize using unauthorized scope

```json
{
    "error": "invalid_scope",
    "error_description": "The scope requested is invalid for this request"
}
```

&nbsp;

### With `client_credentials` grant type

#### Trying to authorize without client secret while the client has a client secret

```json
{
    "error": "invalid_client",
    "error_description": "client credentials are required"
}
```

&nbsp;

#### Trying to authorize with invalid client secret

```json
{
    "error": "invalid_client",
    "error_description": "The client credentials are invalid"
}
```

&nbsp;

### With `password` grant type

#### Most probably trying to authorize without client secret while the client has a client secret

```json
{
    "error": "invalid_client",
    "error_description": "This client is invalid or must authenticate using a client secret"
}
```

&nbsp;

</details>

<details>
<summary>
<h3 style="cursor: pointer">> Frequent errors while trying to access REST APIs</h3>
</summary>

#### Trying to access REST APIs which are in unauthorized scope

e.g. Trying to access `admin` scoped REST APIs using an [`access_token`](https://tools.ietf.org/html/rfc6749#appendix-A.12) which doesn't have access to `admin` scope. Need to recreate [`access_token`](https://tools.ietf.org/html/rfc6749#appendix-A.12) with correct [scope](https://tools.ietf.org/html/rfc6749#appendix-A.4).

```json
{
    "error": "insufficient_scope",
    "error_description": "The request requires higher privileges than provided by the access token"
}
```

&nbsp;
</details>