# 🔧 Oxygen Builder Integration Code

## Overview
This code block allows you to add "Save as Favorite" buttons to your lesson pages built with Oxygen Builder. Students can click these buttons to save lessons to their favorites list using dynamic PHP variables instead of static Oxygen variables.

## 📝 Required Setup

**PHP Variables** (automatically available):
- `$post_id` - Current post/page ID
- `$title` - Page title from WordPress
- `$url` - Current page URL (permalink)

## 💻 Complete Code Block

**Copy the code below into your Oxygen Builder lesson pages:**

<div style="position: relative; background: #f8f8f9; border: 2px solid #e9ecef; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
    <button onclick="copyOxygenCode()" style="position: absolute; top: 15px; right: 15px; background: #007cba; color: white; border: none; padding: 8px 12px; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: 600;">📋 Copy Code</button>
    <pre style="background: #f8f8f9; padding-top: 35px; margin: 0; overflow-x: auto; border-radius: 4px;"><code id="oxygen-code-block"><?php 
$post_id = get_the_ID();
$title   = get_the_title( $post_id );
$url     = get_permalink( $post_id );

echo 'Title: ' . esc_html( $title ) . '<br>';
echo 'URL: ' . esc_url( $url );
?>

<button id="save-lesson-favorite" class="save-favorite-btn">
    ⭐ Save as Favorite
</button>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const saveBtn = document.getElementById('save-lesson-favorite');

    saveBtn.addEventListener('click', function() {
        // Get lesson data from PHP variables
        const lessonData = {
            title: '<?php echo $title; ?>',
            url: '<?php echo $url; ?>',    
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
</style></code></pre>
</div>

<script>
function copyOxygenCode() {
    const codeBlock = document.getElementById('oxygen-code-block');
    const textToCopy = codeBlock.innerText;

    navigator.clipboard.writeText(textToCopy).then(() => {
        // Feedback animation
        const button = event.target;
        const originalText = button.innerHTML;
        button.innerHTML = '✅ Copied!';
        button.style.background = '#28a745';
        
        setTimeout(() => {
            button.innerHTML = originalText;
            button.style.background = '#007cba';
        }, 1500);
    }).catch(err => {
        console.error('Failed to copy:', err);
        alert('Failed to copy code. Please select and copy manually.');
    });
}
</script>

## 🔧 How It Works

### **1. PHP Variable Setup**
```php
<?php 
$post_id = get_the_ID();        // Gets current post ID
$title   = get_the_title( $post_id );  // Gets current post title
$url     = get_permalink( $post_id );  // Gets current post URL
?>
```

### **2. JavaScript Integration**
- Grabs values from PHP variables using `<?php echo $variable; ?>`
- Automatically handles current page title and URL
- No need to manually set Oxygen variables

### **3. Button States**
- **⭐ Save as Favorite** - Default clickable state
- **⏳ Saving...** - Loading state while saving
- **✅ Saved!** - Success confirmation (2 seconds)
- **❌ Error** - Error state with retry option

## 📋 Setup Instructions

### **1. Add to Oxygen Builder**
1. Add a **Code Block** element to your lesson page
2. Click the **📋 Copy Code** button above to copy the complete code
3. Paste the code into the Code Block
4. Save and test the page

### **2. Test Integration**
1. Visit a lesson page (must be logged in)
2. Click **"⭐ Save as Favorite"** button
3. Button should show **"⏳ Saving..."** then **"✅ Saved!"**
4. Check practice hub to verify favorite was saved

## 🎯 Usage Flow

1. **Student visits lesson page** → Sees "Save as Favorite" button
2. **Student clicks button** → Lesson automatically saved with current page data
3. **Student goes to practice hub** → Can select from saved favorites when creating practice items

## 🎨 Visual States

The button has **smooth transitions** and clear visual feedback:

- **🔵 Default Blue** - `#0073aa` - Click to save
- **⚫ Gray** - Disabled/loading state
- **🟢 Green** - Success state `#28a745`
- **🔴 Red** - Error state `#dc3545`

## 🔍 Troubleshooting

### **Common Issues:**

**❌ "Missing lesson title or URL"**
- **Cause:** PHP variables not accessible
- **Solution:** Ensure you're in an Oxygen Code Block with PHP execution enabled

**❌ "Failed to save favorite"**
- **Cause:** REST API endpoint not accessible or user not logged in
- **Solution:** Check plugin is active, user is logged in

**❌ Button doesn't appear**
- **Cause:** JavaScript error or element conflict
- **Solution:** Check browser console (F12) for errors

**❌ "Not logged in" error**
- **Cause:** User session expired
- **Solution:** Refresh page or log in again

### **Testing Steps:**
1. **Open browser developer tools** (F12)
2. **Go to Console tab**
3. **Visit lesson page** and watch for errors
4. **Click button** and check Network tab
5. **Verify API call** to `/wp-json/jph/v1/save-lesson-favorite`

## 📱 Responsive Design

The button works perfectly on:
- ✅ **Desktop computers**
- ✅ **Tablets**
- ✅ **Mobile phones**

## 🎨 Customization

**Change button color:**
```css
.save-favorite-btn {
    background: #your-color !important;
}
```

**Change button size:**
```css
.save-favorite-btn {
    padding: 15px 25px;  /* Larger button */
    font-size: 16px;     /* Larger text */
}
```

**Change hover effect:**
```css
.save-favorite-btn:hover {
    background: #your-hover-color;
    transform: translateY(-2px); /* More pronounced lift */
}
```

## 🔒 Security Features

- ✅ **User authentication required** - Only logged-in users can save favorites
- ✅ **CSRF protection** - WordPress nonce prevents cross-site attacks
- ✅ **Input sanitization** - All data cleaned before database storage
- ✅ **URL validation** - Ensures only valid URLs are saved

## 🚀 API Endpoints

**Required REST API endpoints:**
- `POST /wp-json/jph/v1/save-lesson-favorite` - Save a favorite
- `POST /wp-json/jph/v1/is-favorite` - Check if already saved (optional)

---

**Ready to use!** 🎉 This code automatically grabs the current page's title and URL, so no manual configuration needed in Oxygen Builder.

*This code integrates with the JazzEdge Practice Hub plugin. For technical support, contact the development team.*