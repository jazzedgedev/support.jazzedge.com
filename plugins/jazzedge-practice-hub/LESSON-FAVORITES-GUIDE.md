# 📚 Lesson Favorites Usage Guide

## Overview
Lesson Favorites is a feature that allows students to save and organize their favorite lessons, tutorials, and resources for easy access during practice sessions. This creates a personalized learning library that grows over time.

## 🎯 What are Lesson Favorites?

Lesson Favorites allow students to:
- **Bookmark lessons** from any website (YouTube, Udemy, JazzEdge, etc.)
- **Organize resources** by category (Lesson, Technique, Theory, etc.)
- **Quick access** when creating practice items
- **Build a personal library** of learning resources

## 🔗 Link Requirements

### ✅ Valid URL Examples:
- `https://jazzedge.com/lessons/major-scales`
- `https://youtube.com/watch?v=abc123`
- `https://udemy.com/course/jazz-piano`
- `https://your-site.com/tutorial/chord-progressions`

### ❌ Invalid URL Examples:
- `jazzedge.com/lessons` (missing https://)
- `www.example.com` (missing protocol)
- `file:///local/path` (local files)
- `mailto:email@example.com` (email links)

### 📝 Important Notes:
- **All URLs must start with `http://` or `https://`**
- **URLs must be accessible** to students
- **Test URLs** before adding to ensure they work
- **Avoid local file paths** or restricted content

## 👥 Student Experience

### Adding Favorites:
1. Visit a lesson or course page (built with Oxygen Builder)
2. Click the **"Save as Favorite"** button on the lesson page
3. The lesson is automatically saved to your favorites with:
   - **Title**: Lesson page title
   - **URL**: Current page URL
   - **Category**: Pre-set category (lesson, technique, theory, etc.)
   - **Description**: Lesson description (if provided)

**Note:** Favorites are saved FROM lesson pages, not from the practice hub.

### Viewing Favorites:
- See all favorites displayed as **organized cards**
- Each card shows:
  - Lesson title
  - Category badge
  - Description
  - "View Lesson" button (opens in new tab)
  - "Remove" button

### Using in Practice:
1. When creating a practice item, select **"Choose from lesson favorites"**
2. Pick a favorite from the dropdown
3. The title, category, and description will auto-fill
4. Complete the practice item creation

### Managing Favorites:
- **Edit**: Click on favorites to modify details
- **Remove**: Delete favorites you no longer need
- **Organize**: Use categories to group related content

## 📊 Admin Management

As an admin, you can:

### View All Favorites:
- See favorites from all students
- Filter by user or category
- Monitor usage patterns
- Export favorites data

### Help Students:
- Guide students on proper URL formatting
- Suggest better categorization
- Help organize their learning library
- Troubleshoot issues

### Monitor Usage:
- Track which favorites are most popular
- Identify broken or outdated links
- Understand student learning preferences

## 🏷️ Categories Explained

Students can categorize their favorites:

- **Lesson**: Complete lessons or tutorials
- **Technique**: Specific playing techniques
- **Theory**: Music theory concepts
- **Ear Training**: Listening exercises
- **Repertoire**: Songs and pieces to learn
- **Improvisation**: Improv techniques and exercises
- **Other**: Miscellaneous resources

## 💡 Best Practices

### For Students:
- **Use specific titles** that clearly identify the content
- **Add detailed descriptions** explaining what you'll learn
- **Verify URLs work** before adding to favorites
- **Organize by category** for better practice planning
- **Regular cleanup** of outdated or broken links

### For Admins:
- **Encourage students** to add detailed descriptions
- **Monitor URL validity** and help fix broken links
- **Guide categorization** for better organization
- **Promote usage** during practice sessions
- **Provide examples** of good favorites

## 🔧 Troubleshooting

### Common Issues:

#### ❌ "Error loading favorites"
- **Cause**: Database table not created or REST API issues
- **Solution**: Deactivate and reactivate the plugin to create tables

#### ❌ "Duplicate title" error
- **Cause**: Student already has a favorite with that title
- **Solution**: Use a more specific or different title

#### ❌ URL validation fails
- **Cause**: Invalid URL format or missing protocol
- **Solution**: Ensure URL starts with http:// or https://

#### ❌ "Not logged in" error
- **Cause**: User session expired or not authenticated
- **Solution**: Refresh the page or log in again

#### ❌ Favorites not showing in practice item dropdown
- **Cause**: JavaScript error or API connection issue
- **Solution**: Check browser console for errors, refresh page

### Getting Help:
- Check the **admin instructions** in the Lesson Favorites management page
- Use the **Help button** in the student dashboard
- Contact support if issues persist

## 🚀 Advanced Features

### Integration with Practice Items:
- Seamlessly select favorites when creating practice items
- Auto-fill practice item details from favorite data
- Maintain consistency across practice sessions

### Admin Analytics:
- View total favorites count
- See favorites by category
- Monitor student engagement
- Export data for analysis

### Future Enhancements:
- Bulk import/export favorites
- Favorite sharing between students
- Integration with external learning platforms
- Advanced search and filtering

## 🔧 Oxygen Builder Integration

**For Developers:** To enable the "Save as Favorite" functionality on lesson pages:

1. **Use the provided code block** from `OXYGEN-BUILDER-CODE.md`
2. **Set up required variables** in Oxygen Builder:
   - `lesson_title` - The lesson title
   - `lesson_url` - Current page URL
   - `lesson_category` - Category (lesson, technique, theory, etc.)
   - `lesson_description` - Optional description
3. **Add the code block** to your lesson page templates
4. **Test the integration** to ensure favorites save correctly

**Endpoint:** `/wp-json/jph/v1/save-lesson-favorite`

See the complete implementation details in the `OXYGEN-BUILDER-CODE.md` file.

## 📱 Mobile Experience

The lesson favorites feature is fully responsive and works on:
- **Desktop computers**
- **Tablets**
- **Mobile phones**

All functionality is available on mobile devices with touch-friendly interfaces.

## 🔒 Privacy & Security

- **User-specific**: Students only see their own favorites
- **Admin access**: Admins can view all favorites for management
- **Secure URLs**: Only valid web URLs are accepted
- **No local files**: Prevents access to local system files

## 📈 Success Metrics

Track the success of lesson favorites by monitoring:
- **Total favorites added** per student
- **Category distribution** (most popular types)
- **Usage in practice items** (how often favorites are used)
- **Student engagement** with the feature
- **URL validity** (broken link detection)

---

*This guide is part of the JazzEdge Practice Hub plugin. For technical support or feature requests, contact the development team.*
