# Current Editor & Last Editor Feature Documentation

## Overview

The Current Editor & Last Editor feature provides real-time tracking of who is currently editing projects, who last saved them, and who created them. This helps prevent conflicts when multiple users work on the same project simultaneously.

## Key Features

- **Current Editor Tracking**: Shows who is actively editing a project right now
- **Last Editor Display**: Shows who last saved/updated a project
- **Creator Tracking**: Shows who originally created the project
- **Real-time Updates**: Live polling to keep editor information current
- **Conflict Prevention**: Warns users when someone else is editing
- **Automatic Cleanup**: Handles stale sessions from browser crashes
- **German UI**: User interface text in German

## Architecture

### Design Principles

1. **Log-Only Approach**: No schema changes - uses existing `Log` entity
2. **Context-Based**: All entries use context `'current-editor-project'`
3. **Upsert Pattern**: Update existing entries rather than creating duplicates
4. **Time-Based Expiry**: Sessions expire based on heartbeat timestamps
5. **Self-Healing**: Automatic cleanup of stale sessions

### Database Schema (Existing Log Entity)

```sql
-- Uses existing pv_log table with these key fields:
-- context: 'current-editor-project'
-- category: 'current-editor' | 'last-editor' | 'created-by'
-- action: 'editing' | 'heartbeat' | 'stopped' | 'save' | 'create'
-- value: JSON with project_id, e.g., '{"project_id":123}'
-- username: User who performed the action
-- created_at: Timestamp of action
```

### Categories and Actions

| Category | Actions | Purpose |
|----------|---------|---------|
| `current-editor` | `editing`, `heartbeat`, `stopped` | Track active editing sessions |
| `last-editor` | `save` | Track who last saved the project |
| `created-by` | `create` | Track who created the project |

## Backend Implementation

### 1. Repository Layer (`LogRepository.php`)

```php
// Get current editors with time threshold
getCurrentEditors($timeThresholdMinutes = 2)
getCurrentEditorsForProject($projectId, $excludeUsername = null, $timeThresholdMinutes = 2)

// Get editor history
getLastEditorForProject($projectId)
getCreatedByForProject($projectId)

// Utility methods
findExistingProjectLog($username, $projectId, $category)
findStaleEditingSessions($timeThresholdMinutes = 5)
```

### 2. Service Layer (`LogService.php`)

```php
// Project lifecycle logging
logProjectCreate($projectId)           // One-time creation log
logProjectSave($projectId)             // Update last-editor on save

// Editing session management
logProjectEditingStart($projectId)     // Start editing session
logProjectEditingHeartbeat($projectId) // Keep session alive
logProjectEditingStop($projectId)      // End editing session
logProjectEditingTakeover($projectId)  // Take over from another user

// Maintenance
cleanupStaleEditingSessions($timeThresholdMinutes = 5)
```

### 3. API Endpoints (`ApiProjectsController.php`)

#### GET `/api/v1/projects/editors`
Returns all current editors across all projects.

**Response:**
```json
{
  "123": {
    "username": "john.doe@example.com",
    "created_at": "2024-01-15T10:30:00+00:00"
  },
  "456": {
    "username": "jane.smith@example.com", 
    "created_at": "2024-01-15T10:25:00+00:00"
  }
}
```

#### GET `/api/v1/projects/{id}/editor-info`
Returns detailed editor information for a specific project.

**Response:**
```json
{
  "current_editor": {
    "username": "john.doe@example.com",
    "created_at": "2024-01-15T10:30:00+00:00"
  },
  "last_editor": {
    "username": "jane.smith@example.com",
    "created_at": "2024-01-15T09:45:00+00:00"
  },
  "created_by": {
    "username": "admin@example.com",
    "created_at": "2024-01-10T14:20:00+00:00"
  }
}
```

#### POST `/api/v1/projects/{id}/editing`
Start editing session for a project.

**Responses:**
- **Success:** `{"success": true, "message": "Editing session started"}`
- **Conflict:** `{"conflict": true, "current_editor": {...}, "message": "Another user is currently editing this project"}`

#### DELETE `/api/v1/projects/{id}/editing`
Stop editing session for a project.

**Response:** `{"success": true, "message": "Editing session stopped"}`

#### POST `/api/v1/projects/{id}/heartbeat`
Send heartbeat to keep editing session alive.

**Response:** `{"success": true, "message": "Heartbeat received"}`

#### POST `/api/v1/projects/{id}/editing/takeover`
Force take over editing session from another user.

**Response:** `{"success": true, "message": "Editing session taken over"}`

## Frontend Implementation

### 1. Projects List (`Projects.vue`)

**Features:**
- Polls for current editors every 15 seconds
- Shows "Bearbeitung" column with current editor info
- Orange styling for active editing indicators

**Key Methods:**
```javascript
// Periodic polling
pollCurrentEditors() // Runs every 15 seconds

// Display logic
getCurrentEditorForProject(projectId) // Get editor for specific project
```

### 2. Project Editor (`Project.vue`)

**Features:**
- Automatic session management (start/stop/heartbeat)
- Editor information display section
- Fixed position current editor indicator (bottom-right)
- Conflict detection and takeover functionality

**Session Lifecycle:**
```javascript
mounted() {
  this.startEditingSession(); // Start when component loads
}

beforeUnmount() {
  this.stopEditingSession(); // Stop when component unloads
}

window.addEventListener('beforeunload', this.handleBeforeUnload); // Handle browser close
```

**Polling & Heartbeat:**
```javascript
// Send heartbeat every 15 seconds
startHeartbeat() // setInterval(() => sendHeartbeat(), 15000)

// Poll editor info every 10 seconds  
startEditorInfoPolling() // setInterval(() => loadEditorInfo(), 10000)
```

**UI Elements:**
- Editor info section (shows current/last/created by)
- Fixed position indicator (bottom-right corner)
- Takeover modal (when conflicts detected)

## Configuration

### Time Thresholds

| Setting | Value | Purpose |
|---------|-------|---------|
| Heartbeat Interval | 15 seconds | How often frontend sends heartbeats |
| Current Editor Threshold | 2 minutes | How long to consider someone "current" |
| Stale Session Cleanup | 5 minutes | When to mark sessions as stopped |
| Editor Info Polling | 10 seconds | How often to refresh editor info |
| Current Editors Polling | 15 seconds | How often to refresh all editors |

### Buffer Zones

- **Active Session**: 0-2 minutes since last heartbeat
- **Grace Period**: 2-5 minutes (no longer shown as current, but not cleaned up)
- **Cleanup Zone**: 5+ minutes (session marked as stopped)

## Stale Session Handling

### The Problem
When users close browsers unexpectedly, editing sessions remain as "heartbeat" entries in the database, preventing other users from editing.

### The Solution
**Automatic Cleanup with Different Thresholds:**

1. **Display Logic (2 minutes)**: Users only appear as "current editors" if their last heartbeat was within 2 minutes
2. **Cleanup Logic (5 minutes)**: Sessions are only marked as "stopped" if they're older than 5 minutes
3. **Buffer Zone**: 3-minute safety buffer prevents false positives

### Cleanup Triggers
Automatic cleanup runs before:
- `getCurrentEditors()` - Getting all current editors
- `getEditorInfo()` - Getting editor info for a project
- `startEditing()` - Starting a new editing session

## Error Handling

### Frontend
- Network failures are logged but don't break functionality
- Missing API responses default to empty state
- Session start failures show user-friendly messages

### Backend  
- Invalid project IDs return 404 errors
- Authentication failures return 401 errors
- Database errors are logged and return 500 errors

## Testing Scenarios

### Normal Flow
1. User A opens project → session starts
2. User A edits → heartbeats sent every 15s
3. User A saves → last-editor updated
4. User A closes project → session stops

### Conflict Flow
1. User A starts editing project
2. User B tries to edit same project
3. User B sees conflict modal with takeover option
4. User B can take over or cancel

### Crash Recovery
1. User A editing, browser crashes
2. After 2 minutes: User A no longer appears as current editor
3. After 5 minutes: User A's session marked as stopped
4. Other users can edit normally

## Troubleshooting

### Issue: Editor info not updating
**Solution:** Check browser console for API errors, verify polling intervals

### Issue: Users shown as editing when they're not
**Solution:** Check heartbeat intervals and time thresholds, may need to adjust cleanup timing

### Issue: Takeover modal not appearing
**Solution:** Verify `getCurrentEditorsForProject` excludes current user, check conflict detection logic

### Issue: Stale sessions not cleaning up
**Solution:** Verify cleanup thresholds (5 minutes), check that cleanup methods are being called

### Issue: Database growing with log entries
**Solution:** Consider implementing log rotation or archival for old editor tracking entries

## German UI Text

```javascript
// Example translations used
"Aktuell bearbeitet von": "Currently being edited by"
"Bearbeitung übernehmen": "Take over editing"
"Zuletzt bearbeitet": "Last edited"
"Erstellt von": "Created by"
"Jemand bearbeitet bereits": "Someone is already editing"
```

## Performance Considerations

### Database
- Indexed columns: `context`, `category`, `username`, `created_at`
- Regular cleanup prevents table bloat
- Time-based queries use indexes efficiently

### Frontend
- Polling intervals balance real-time updates with server load
- API calls are debounced and cached when possible
- Cleanup on component unmount prevents memory leaks

### Network
- Heartbeats are lightweight (minimal payload)
- Editor info polling is project-specific
- Failed requests don't retry aggressively

## Future Enhancements

### Potential Improvements
1. **WebSocket Integration**: Replace polling with real-time push notifications
2. **User Presence**: Show when users are viewing (not just editing)
3. **Edit Location**: Track which section of project is being edited
4. **Collaborative Editing**: Real-time collaborative text editing
5. **Edit History**: Detailed audit trail of all changes
6. **Conflict Resolution**: Merge conflicting changes automatically
7. **Offline Support**: Handle offline editing scenarios

### Scalability Considerations
- Consider Redis for session storage in high-traffic environments
- Implement proper log rotation for production systems
- Add monitoring for cleanup performance
- Consider database partitioning for large installations
