# 🔧 Oxygen Builder Integration Code

## Overview
This code block allows you to add "Save as Favorite" buttons to your lesson pages built with Oxygen Builder. Students can click these buttons to save lessons to their favorites list.

## 📝 Required Variables

You need to set up these variables in Oxygen Builder:

- **`lesson_title`** - The lesson title (e.g., "Major Scale Practice")
- **`lesson_url`** - Current page URL (use `{{current_url}}` or similar)
- **`lesson_category`** - Category (lesson, technique, theory, ear-training, repertoire, improvisation, other)
- **`lesson_description`** - Optional description of what students will learn

## 💻 Complete Code Block

Add this code block to your Oxygen Builder lesson pages:

```html
<button id="save-lesson-favorite" class="save-favorite-btn">
    ⭐ Save as Favorite
</button>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const saveBtn = document.getElementById('save-lesson-favorite');
    
    saveBtn.addEventListener('click', function() {
        // Get lesson data from Oxygen variables
        const lessonData = {
            title: '{{lesson_title}}', // Replace with actual Oxygen variable
            url: '{{lesson_url}}',     // Replace with actual Oxygen variable  
            category: '{{lesson_category}}', // Replace with actual Oxygen variable
            description: '{{lesson_description}}' // Replace with actual Oxygen variable
        };
        
        // Validate required fields
        if (!lessonData.title || !lessonData.url) {
            alert('Missing lesson title or URL');
            return;
        }
        
        // Show loading state
        saveBtn.innerHTML = '⏳ Saving...';
        saveBtn.disabled = true;
        
        // Send to REST API
        fetch('/wp-json/jph/v1/save-lesson-favorite', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
            },
            body: JSON.stringify(lessonData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                saveBtn.innerHTML = '✅ Saved!';
                saveBtn.style.background = '#28a745';
                setTimeout(() => {
                    saveBtn.innerHTML = '⭐ Save as Favorite';
                    saveBtn.style.background = '';
                    saveBtn.disabled = false;
                }, 2000);
            } else {
                throw new Error(data.message || 'Failed to save favorite');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            saveBtn.innerHTML = '❌ Error';
            saveBtn.style.background = '#dc3545';
            setTimeout(() => {
                saveBtn.innerHTML = '⭐ Save as Favorite';
                saveBtn.style.background = '';
                saveBtn.disabled = false;
            }, 2000);
        });
    });
});
</script>

<style>
.save-favorite-btn {
    background: #0073aa;
    color: white;
    border: none;
    padding: 12px 20px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.2s ease;
    display: inline-block;
    text-decoration: none;
}

.save-favorite-btn:hover {
    background: #005a87;
    transform: translateY(-1px);
}

.save-favorite-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}
</style>
```

## 🔧 Alternative: Form Post Method

If you prefer using a form post instead of AJAX, here's the alternative approach:

```html
<form id="save-favorite-form" method="POST" action="/wp-json/jph/v1/save-lesson-favorite">
    <input type="hidden" name="title" value="{{lesson_title}}">
    <input type="hidden" name="url" value="{{lesson_url}}">
    <input type="hidden" name="category" value="{{lesson_category}}">
    <input type="hidden" name="description" value="{{lesson_description}}">
    <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('wp_rest'); ?>">
    
    <button type="submit" class="save-favorite-btn">
        ⭐ Save as Favorite
    </button>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('save-favorite-form');
    const submitBtn = form.querySelector('button[type="submit"]');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Show loading state
        submitBtn.innerHTML = '⏳ Saving...';
        submitBtn.disabled = true;
        
        // Submit form
        fetch(form.action, {
            method: 'POST',
            body: new FormData(form)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                submitBtn.innerHTML = '✅ Saved!';
                submitBtn.style.background = '#28a745';
                setTimeout(() => {
                    submitBtn.innerHTML = '⭐ Save as Favorite';
                    submitBtn.style.background = '';
                    submitBtn.disabled = false;
                }, 2000);
            } else {
                throw new Error(data.message || 'Failed to save favorite');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            submitBtn.innerHTML = '❌ Error';
            submitBtn.style.background = '#dc3545';
            setTimeout(() => {
                submitBtn.innerHTML = '⭐ Save as Favorite';
                submitBtn.style.background = '';
                submitBtn.disabled = false;
            }, 2000);
        });
    });
});
</script>
```

## 📋 Setup Instructions

### 1. Create Oxygen Variables
In Oxygen Builder, create these variables:
- `lesson_title` - Text field for lesson title
- `lesson_url` - Use `{{current_url}}` or similar
- `lesson_category` - Select field with options: lesson, technique, theory, ear-training, repertoire, improvisation, other
- `lesson_description` - Textarea for description

### 2. Add Code Block
- Add a Code Block element to your lesson pages
- Paste the code above
- Replace the variable placeholders with actual Oxygen variables

### 3. Test the Integration
- Visit a lesson page
- Click "Save as Favorite"
- Check the practice hub to see if the favorite appears

## 🎯 Usage Flow

1. **Student visits lesson page** → Sees "Save as Favorite" button
2. **Student clicks button** → Lesson is saved to their favorites
3. **Student goes to practice hub** → Can choose from saved favorites when creating practice items
4. **Student creates practice item** → Selects from dropdown of saved favorites

## 🔍 Troubleshooting

### Common Issues:

**❌ "Missing lesson title or URL"**
- **Cause:** Oxygen variables not set up correctly
- **Solution:** Verify variable names match exactly

**❌ "Failed to save favorite"**
- **Cause:** REST API endpoint not accessible
- **Solution:** Check that the plugin is active and REST API is working

**❌ "Not logged in" error**
- **Cause:** User not authenticated
- **Solution:** Ensure user is logged in before accessing lesson pages

**❌ Button doesn't respond**
- **Cause:** JavaScript error or missing elements
- **Solution:** Check browser console for errors

### Testing Steps:
1. Open browser developer tools (F12)
2. Go to Console tab
3. Click "Save as Favorite" button
4. Check for any error messages
5. Go to Network tab to see if API call is made

## 📱 Responsive Design

The button is fully responsive and works on:
- Desktop computers
- Tablets
- Mobile phones

## 🎨 Customization

You can customize the button appearance by modifying the CSS:

```css
.save-favorite-btn {
    background: #your-color; /* Change button color */
    padding: 15px 25px;      /* Change button size */
    font-size: 16px;         /* Change text size */
    border-radius: 8px;      /* Change corner radius */
}
```

## 🔒 Security Notes

- All data is sanitized before saving
- URLs are validated to ensure they're proper web addresses
- User authentication is required
- CSRF protection via WordPress nonce

---

*This code integrates with the JazzEdge Practice Hub plugin. For technical support, contact the development team.*
