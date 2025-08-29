# Posts API Documentation

This document describes the API endpoint for retrieving posts.

## Endpoints

### Get All Posts

Retrieves a list of all posts with their associated user information.

**URL**: `/api/posts`

**Method**: `GET`

**Auth required**: No

**Permissions required**: None

#### Success Response

**Code**: `200 OK`

**Content example**:

```json
{
  "data": [
    {
      "id": 1,
      "title": "Sample Post Title",
      "content": "This is the content of the post...",
      "user_id": 1,
      "slug": "sample-post-title",
      "excerpt": "This is a short excerpt...",
      "status": "published",
      "created_at": "2025-08-08T14:00:00.000000Z",
      "updated_at": "2025-08-08T14:00:00.000000Z",
      "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "email_verified_at": null,
        "created_at": "2025-08-08T14:00:00.000000Z",
        "updated_at": "2025-08-08T14:00:00.000000Z"
      }
    }
  ],
  "message": "Posts retrieved successfully"
}
```

## Testing the API

You can test this API endpoint using tools like:

1. **cURL**:
   ```
   curl -X GET http://your-domain.com/api/posts
   ```

2. **Postman**: Create a GET request to `http://your-domain.com/api/posts`

3. **Browser**: Navigate to `http://your-domain.com/api/posts` in your web browser

## Notes

- The endpoint returns all posts in the database along with the user who created each post.
- Posts are not paginated in the current implementation.
- This is a public endpoint that doesn't require authentication.
