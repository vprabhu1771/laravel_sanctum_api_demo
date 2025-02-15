POST -> http://192.168.1.122:8000/api/register

Body -> Raw -> JSON

{
    "name": "john doe",
    "email": "john@gmail.com",
    "password": "john",
    "device_name": "android"
}

Response

{
    "message": "User registered successfully",
    "user": {
        "name": "john doe",
        "email": "john@gmail.com",
        "updated_at": "2025-02-15T04:25:36.000000Z",
        "created_at": "2025-02-15T04:25:36.000000Z",
        "id": 2,
        "avatar": "https://gravatar.com/avatar/1f9d9a9efc2f523b2f09629444632b5c"
    }
}