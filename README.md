# Mother's Day Website

This is a static website, so it works on GitHub Pages and can be edited in PhpStorm.

## How to edit the pictures

The site photos are already in `assets/images/`:

- `mom-birthday.jpg`
- `mom-party.jpg`
- `mom-family.jpg`
- `mom-formal.jpg`
- `mom-memory.jpg`

To replace one later, put the new photo in `assets/images/`, then update the `src` values in `index.html` and the matching files inside `messages/`.

Example:

```html
<img src="assets/images/mom-photo-1.jpg" alt="Mom smiling at dinner">
```

On a message page, the path starts with `../`:

```html
<img src="../assets/images/mom-photo-1.jpg" alt="Mom smiling at dinner">
```

## How to publish on GitHub Pages

1. Push this folder to a GitHub repository.
2. Go to the repository settings.
3. Open Pages.
4. Set the source to the main branch and the root folder.
5. Save, then open the link GitHub gives you.
