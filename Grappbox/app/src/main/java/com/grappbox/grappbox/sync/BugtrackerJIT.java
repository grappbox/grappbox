package com.grappbox.grappbox.sync;

import android.accounts.NetworkErrorException;
import android.app.Activity;
import android.app.IntentService;
import android.content.ContentValues;
import android.content.Intent;
import android.content.OperationApplicationException;
import android.database.Cursor;
import android.database.SQLException;
import android.net.Uri;
import android.os.Bundle;
import android.support.annotation.Nullable;
import android.support.v4.os.ResultReceiver;
import android.util.Log;

import com.grappbox.grappbox.BuildConfig;
import com.grappbox.grappbox.Utils;
import com.grappbox.grappbox.data.GrappboxContract;
import com.grappbox.grappbox.model.BugModel;
import com.grappbox.grappbox.receiver.BugReceiver;
import com.grappbox.grappbox.singleton.Session;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;
import java.net.HttpURLConnection;
import java.net.URL;
import java.text.ParseException;
import java.util.ArrayList;
import java.util.List;

import static com.grappbox.grappbox.sync.GrappboxJustInTimeService.BUNDLE_KEY_ERROR_MSG;
import static com.grappbox.grappbox.sync.GrappboxJustInTimeService.EXTRA_PROJECT_ID;
import static com.grappbox.grappbox.sync.GrappboxJustInTimeService.EXTRA_RESPONSE_RECEIVER;
import static com.grappbox.grappbox.sync.GrappboxJustInTimeService.EXTRA_USER_ID;

public class BugtrackerJIT extends IntentService {
    public static final String ACTION_SYNC_BUGS = "com.grappbox.action.ACTION_SYNC_BUGS";
    public static final String ACTION_SYNC_BUG_COMMENT = "com.grappbox.action.ACTION_SYNC_BUG_COMMENT";
    public static final String ACTION_POST_COMMENT = "com.grappbox.action.ACTION_POST_COMMENT";
    public static final String ACTION_EDIT_COMMENT = "com.grappbox.action.ACTION_EDIT_COMMENT";
    public static final String ACTION_DELETE_COMMENT = "com.grappbox.action.ACTION_DELETE_COMMENT";
    public static final String ACTION_CLOSE_BUG = "com.grappbox.action.ACTION_CLOSE_BUG";
    public static final String ACTION_REOPEN_BUG = "com.grappbox.action.ACTION_REOPEN_BUG";
    public static final String ACTION_CREATE_BUG = "com.grappbox.actionc.ACTION_CREATE_BUG";
    public static final String ACTION_EDIT_BUG = "com.grappbox.action.ACTION_EDIT_BUG";
    public static final String ACTION_SYNC_TAGS = "com.grappbox.action.ACTION_SYNC_TAGS";
    public static final String ACTION_CREATE_TAG = "com.grappbox.action.ACTION_CREATE_TAG";
    public static final String ACTION_EDIT_BUGTAG = "com.grappbox.action.ACTION_EDIT_BUGTAG";
    public static final String ACTION_REMOVE_BUGTAG = "com.grappbox.action.ACTION_REMOVE_BUGTAG";
    public static final String ACTION_SET_PARTICIPANT = "com.grappbox.action.ACTION_SET_PARTICIPANT";

    public static final String EXTRA_BUG_ID = "com.grappbox.extra.bug_id";

    public static final String CATEGORY_CLOSED = "com.grappbox.category.CATEGORY_CLOSED";

    public BugtrackerJIT() {
        super("Bugtracker Just In Time");
    }

    @SuppressWarnings("unchecked")
    @Override
    protected void onHandleIntent(Intent intent) {
        Log.e("TEST", "HandleIntent");
        if (intent != null) {
            final String action = intent.getAction();
            ResultReceiver responseObserver = intent.hasExtra(EXTRA_RESPONSE_RECEIVER) ? (ResultReceiver) intent.getParcelableExtra(EXTRA_RESPONSE_RECEIVER) : null;
            switch (action){
                case ACTION_SYNC_BUGS:
                    boolean isClosedSyncing = intent.getCategories() != null && intent.getCategories().contains(CATEGORY_CLOSED);
                    handleBugsSync(intent.getLongExtra(EXTRA_USER_ID, -1), intent.getLongExtra(EXTRA_PROJECT_ID, -1), intent.getIntExtra(GrappboxJustInTimeService.EXTRA_OFFSET, 0), intent.getIntExtra(GrappboxJustInTimeService.EXTRA_LIMIT, 50), isClosedSyncing, responseObserver);
                    break;
                case ACTION_SYNC_BUG_COMMENT:
                    handleBugsCommentsSync(intent.getLongExtra(EXTRA_USER_ID, -1), intent.getLongExtra(EXTRA_PROJECT_ID, -1), intent.getLongExtra(EXTRA_BUG_ID, -1), responseObserver);
                    break;
                case ACTION_POST_COMMENT:
                    handleBugPostComment(intent.getLongExtra(EXTRA_PROJECT_ID, -1), intent.getLongExtra(EXTRA_BUG_ID, -1), intent.getStringExtra(GrappboxJustInTimeService.EXTRA_MESSAGE), responseObserver);
                    break;
                case ACTION_EDIT_COMMENT:
                    handleBugEditComment(intent.getLongExtra(EXTRA_PROJECT_ID, -1), intent.getLongExtra(EXTRA_BUG_ID, -1), intent.getLongExtra(GrappboxJustInTimeService.EXTRA_COMMENT_ID, -1),intent.getStringExtra(GrappboxJustInTimeService.EXTRA_MESSAGE), responseObserver);
                    break;
                case ACTION_DELETE_COMMENT:
                    handleBugCloseComment(intent.getLongExtra(GrappboxJustInTimeService.EXTRA_COMMENT_ID, -1), responseObserver);
                    break;
                case ACTION_CLOSE_BUG:
                    handleBugClose(intent.getLongExtra(EXTRA_BUG_ID, -1), responseObserver);
                    break;
                case ACTION_REOPEN_BUG:
                    handleBugReopenComment(intent.getLongExtra(EXTRA_BUG_ID, -1), responseObserver);
                    break;
                case ACTION_CREATE_BUG:
                    handleBugCreate(false, intent.getLongExtra(EXTRA_PROJECT_ID, -1), intent.getStringExtra(GrappboxJustInTimeService.EXTRA_TITLE), intent.getStringExtra(GrappboxJustInTimeService.EXTRA_DESCRIPTION), intent.getBooleanExtra(GrappboxJustInTimeService.EXTRA_CLIENT_ACTION, false), responseObserver);
                    break;
                case ACTION_EDIT_BUG:
                    handleBugCreate(true, intent.getLongExtra(EXTRA_BUG_ID, -1), intent.getStringExtra(GrappboxJustInTimeService.EXTRA_TITLE), intent.getStringExtra(GrappboxJustInTimeService.EXTRA_DESCRIPTION), intent.getBooleanExtra(GrappboxJustInTimeService.EXTRA_CLIENT_ACTION, false), responseObserver);
                    break;
                case ACTION_SYNC_TAGS:
                    handleTagSync(intent.getLongExtra(EXTRA_PROJECT_ID, -1));
                    break;
                case ACTION_CREATE_TAG:
                    handleCreateTag(intent.getLongExtra(EXTRA_PROJECT_ID, -1), intent.getLongExtra(EXTRA_BUG_ID, -1), intent.getStringExtra(GrappboxJustInTimeService.EXTRA_TITLE), intent.getStringExtra(GrappboxJustInTimeService.EXTRA_COLOR), responseObserver);
                    break;
                case ACTION_EDIT_BUGTAG:
                    handleEditBugTag(intent.getLongExtra(EXTRA_BUG_ID, -1), intent.getLongExtra(GrappboxJustInTimeService.EXTRA_TAG_ID, -1), responseObserver);
                    break;
                case ACTION_REMOVE_BUGTAG:
                    handleRemoveBugTag(intent.getLongExtra(EXTRA_BUG_ID, -1), intent.getLongExtra(GrappboxJustInTimeService.EXTRA_TAG_ID, -1), responseObserver);
                    break;
                case ACTION_SET_PARTICIPANT:
                    Bundle arg = intent.getBundleExtra(GrappboxJustInTimeService.EXTRA_BUNDLE);
                    handleBugSetParticipant(intent.getLongExtra(EXTRA_BUG_ID, -1), (List<Long>) arg.getSerializable(GrappboxJustInTimeService.EXTRA_ADD_PARTICIPANT), (List<Long>) arg.getSerializable(GrappboxJustInTimeService.EXTRA_DEL_PARTICIPANT), responseObserver);
                    break;
                default:
                    throw new UnsupportedOperationException("404 Action Not Found");
            }
        }
    }

    private void handleBugsSync(long localUID, long localPID, int offset, int limit, boolean isSyncingClosedBugs, ResultReceiver responseObserver) {
        String apiToken = Utils.Account.getAuthTokenService(this, null);
        if (apiToken.isEmpty() || localUID == -1 || localPID == -1)
            return;
        HttpURLConnection connection = null;
        String returnedJson;
        Cursor grappboxProjectId = getContentResolver().query(GrappboxContract.ProjectEntry.buildProjectWithLocalIdUri(localPID), new String[]{GrappboxContract.ProjectEntry.COLUMN_GRAPPBOX_ID}, null, null, null);

        if (grappboxProjectId == null || !grappboxProjectId.moveToFirst())
            return;
        try {
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/bugtracker/tickets/"+ (isSyncingClosedBugs ? "closed/" : "opened/")+grappboxProjectId.getString(0)+"/"+String.valueOf(offset)+"/" + String.valueOf(limit));
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestProperty("Authorization", apiToken);
            connection.setRequestMethod("GET");
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson != null && !returnedJson.isEmpty()){
                JSONObject json = new JSONObject(returnedJson);
                if (Utils.Errors.checkAPIError(json)){
                    if (responseObserver != null)
                    {
                        Bundle answer = new Bundle();
                        answer.putString(BUNDLE_KEY_ERROR_MSG, Utils.Errors.getClientMessageFromErrorCode(this, json.getJSONObject("info").getString("return_code")));
                        responseObserver.send(Activity.RESULT_CANCELED, answer);
                    }
                    throw new NetworkErrorException(Utils.Errors.ERROR_API_GENERIC);
                }
                else {
                    JSONArray bugsData = json.getJSONObject("data").getJSONArray("array");
                    if (bugsData.length() != 0){

                        for (int i = 0; i < bugsData.length(); ++i) {
                            ContentValues currentValue = new ContentValues();
                            JSONObject bug = bugsData.getJSONObject(i);
                            JSONArray bugTag = bug.getJSONArray("tags");
                            JSONArray bugUser = bug.getJSONArray("users");

                            String grappboxCID = bug.getJSONObject("creator").getString("id");
                            Cursor creatorId = getContentResolver().query(GrappboxContract.UserEntry.CONTENT_URI, new String[] {GrappboxContract.UserEntry._ID}, GrappboxContract.UserEntry.COLUMN_GRAPPBOX_ID + "=?", new String[]{grappboxCID}, null);

                            if (creatorId == null || !creatorId.moveToFirst())
                            {
                                GrappboxJustInTimeService.handleUserDetailSync(this, grappboxCID);
                                creatorId = getContentResolver().query(GrappboxContract.UserEntry.CONTENT_URI, new String[] {GrappboxContract.UserEntry._ID}, GrappboxContract.UserEntry.COLUMN_GRAPPBOX_ID + "=?", new String[]{grappboxCID}, null);
                                if (creatorId == null || !creatorId.moveToFirst()){
                                    continue;
                                }
                            }
                            currentValue.put(GrappboxContract.BugEntry.COLUMN_GRAPPBOX_ID, bug.getString("id"));
                            currentValue.put(GrappboxContract.BugEntry.COLUMN_LOCAL_CREATOR_ID, creatorId.getLong(0));
                            creatorId.close();
                            currentValue.put(GrappboxContract.BugEntry.COLUMN_LOCAL_PROJECT_ID, localPID);
                            currentValue.put(GrappboxContract.BugEntry.COLUMN_TITLE, bug.getString("title"));
                            currentValue.put(GrappboxContract.BugEntry.COLUMN_DESCRIPTION, bug.getString("description"));
                            currentValue.putNull(GrappboxContract.BugEntry.COLUMN_LOCAL_PARENT_ID);
                            //TODO : manage client origin
                            String last_edited = Utils.Date.getDateFromGrappboxAPIToUTC(bug.getString(bug.isNull("editedAt") ? "createdAt" : "editedAt"));
                            currentValue.put(GrappboxContract.BugEntry.COLUMN_DATE_LAST_EDITED_UTC, last_edited);
                            if (!bug.isNull("deletedAt")){
                                currentValue.put(GrappboxContract.BugEntry.COLUMN_DATE_DELETED_UTC, Utils.Date.getDateFromGrappboxAPIToUTC(bug.getString("deletedAt")));
                            }
                            Uri bugUri = getContentResolver().insert(GrappboxContract.BugEntry.CONTENT_URI, currentValue);
                            if (bugUri == null)
                                continue;
                            long bugId = Long.parseLong(bugUri.getLastPathSegment());
                            if (bugId < 0)
                                continue;
                            Intent syncComments = new Intent(this, GrappboxJustInTimeService.class);
                            syncComments.setAction(ACTION_SYNC_BUG_COMMENT);
                            syncComments.putExtra(EXTRA_PROJECT_ID, localPID);
                            syncComments.putExtra(EXTRA_USER_ID, localUID);
                            syncComments.putExtra(EXTRA_RESPONSE_RECEIVER, responseObserver);
                            syncComments.putExtra(EXTRA_BUG_ID, bugId);
                            startService(syncComments);

                            ArrayList<Long> existingTags = new ArrayList<>();
                            for (int j = 0; j < bugTag.length(); ++j) {
                                JSONObject currentTag = bugTag.getJSONObject(j);
                                ContentValues tagValue = new ContentValues();
                                ContentValues tagAssignValue = new ContentValues();

                                tagValue.put(GrappboxContract.BugtrackerTagEntry.COLUMN_GRAPPBOX_ID, currentTag.getString("id"));
                                tagValue.put(GrappboxContract.BugtrackerTagEntry.COLUMN_LOCAL_PROJECT_ID, localPID);
                                tagValue.put(GrappboxContract.BugtrackerTagEntry.COLUMN_NAME, currentTag.getString("name"));
                                tagValue.put(GrappboxContract.BugtrackerTagEntry.COLUMN_COLOR, currentTag.getString("color").startsWith("#") ? currentTag.getString("color") : "#" + currentTag.getString("color"));
                                Uri tagURI = getContentResolver().insert(GrappboxContract.BugtrackerTagEntry.CONTENT_URI, tagValue);
                                if (tagURI == null)
                                    continue;
                                tagAssignValue.put(GrappboxContract.BugTagEntry.COLUMN_LOCAL_BUG_ID, bugId);
                                tagAssignValue.put(GrappboxContract.BugTagEntry.COLUMN_LOCAL_TAG_ID, Long.parseLong(tagURI.getLastPathSegment()));
                                Uri bugtagUri = getContentResolver().insert(GrappboxContract.BugTagEntry.CONTENT_URI, tagAssignValue);
                                if (bugtagUri == null)
                                    continue;
                                long bugtagId = Long.parseLong(bugtagUri.getLastPathSegment());
                                existingTags.add(bugtagId);
                            }
                            String selection = GrappboxContract.BugTagEntry.COLUMN_LOCAL_BUG_ID + "=?"+ (existingTags.size() > 0 ? " AND " + GrappboxContract.BugTagEntry._ID + " NOT IN (" : "");
                            if (existingTags.size() > 0){
                                boolean isFirst = true;
                                for (Long idTag : existingTags){
                                    selection += isFirst ? idTag.toString() : "," + idTag.toString();
                                    isFirst = false;
                                }
                                selection += ")";
                            }
                            getContentResolver().delete(GrappboxContract.BugTagEntry.CONTENT_URI, selection, new String[]{String.valueOf(bugId)});
                            //insert users
                            ArrayList<Long> existingUsers = new ArrayList<>();
                            for (int j = 0; j < bugUser.length(); ++j) {
                                JSONObject currentUser = bugUser.getJSONObject(j);
                                Cursor userCursor = getContentResolver().query(GrappboxContract.UserEntry.CONTENT_URI, new String[]{GrappboxContract.UserEntry._ID}, GrappboxContract.UserEntry.COLUMN_GRAPPBOX_ID + "=?", new String[]{currentUser.getString("id")}, null);
                                ContentValues currentUserAssignation = new ContentValues();

                                if (userCursor == null || !userCursor.moveToFirst())
                                {
                                    GrappboxJustInTimeService.handleUserDetailSync(this, currentUser.getString("id"));
                                    userCursor = getContentResolver().query(GrappboxContract.UserEntry.CONTENT_URI, new String[]{GrappboxContract.UserEntry._ID}, GrappboxContract.UserEntry.COLUMN_GRAPPBOX_ID + "=?", new String[]{currentUser.getString("id")}, null);
                                    if (userCursor == null || !userCursor.moveToFirst()){
                                        continue;
                                    }
                                }
                                currentUserAssignation.put(GrappboxContract.BugAssignationEntry.COLUMN_LOCAL_BUG_ID, bugId);
                                currentUserAssignation.put(GrappboxContract.BugAssignationEntry.COLUMN_LOCAL_USER_ID, userCursor.getLong(0));
                                Uri userEntryURI = getContentResolver().insert(GrappboxContract.BugAssignationEntry.CONTENT_URI, currentUserAssignation);
                                if (userEntryURI == null)
                                    continue;
                                long userEntryId = Long.parseLong(userEntryURI.getLastPathSegment());
                                existingUsers.add(userEntryId);
                                userCursor.close();
                            }
                            selection = GrappboxContract.BugTagEntry.COLUMN_LOCAL_BUG_ID + "=?" + (existingUsers.size() > 0 ? " AND " + GrappboxContract.BugAssignationEntry._ID + " NOT IN (" : "");
                            if (existingUsers.size() > 0){
                                boolean isFirst = true;
                                for (Long idUser : existingUsers){
                                    selection += isFirst ? idUser.toString() : "," + idUser.toString();
                                    isFirst = false;
                                }
                                selection += ")";
                            }
                            getContentResolver().delete(GrappboxContract.BugAssignationEntry.CONTENT_URI, selection, new String[]{String.valueOf(bugId)});
                        }
                    }
                }
            }
        } catch (JSONException | ParseException | NetworkErrorException | IOException e) {
            e.printStackTrace();
        } finally {
            grappboxProjectId.close();
            if (connection != null)
                connection.disconnect();
            if (responseObserver != null){
                responseObserver.send(Activity.RESULT_OK, null);
            }
        }
    }

    private void handleBugsCommentsSync(long localUID, long localPID, long bugId, @Nullable ResultReceiver responseObserver){
        String apiToken = Utils.Account.getAuthTokenService(this, null);
        if (apiToken.isEmpty() || localUID == -1 || localPID == -1)
            return;
        HttpURLConnection connection = null;
        String returnedJson;
        Cursor grappboxProjectId = getContentResolver().query(GrappboxContract.ProjectEntry.buildProjectWithLocalIdUri(localPID), new String[]{GrappboxContract.ProjectEntry.COLUMN_GRAPPBOX_ID}, null, null, null);
        Cursor grappboxBugId = getContentResolver().query(GrappboxContract.BugEntry.CONTENT_URI, new String[]{GrappboxContract.BugEntry.COLUMN_GRAPPBOX_ID}, GrappboxContract.BugEntry._ID + "=?", new String[]{String.valueOf(bugId)}, null);
        if (grappboxProjectId == null || !grappboxProjectId.moveToFirst() || grappboxBugId == null || !grappboxBugId.moveToFirst())
            return;
        try {
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/bugtracker/comments/"+grappboxBugId.getString(0));
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestProperty("Authorization", apiToken);
            connection.setRequestMethod("GET");
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson != null && !returnedJson.isEmpty()){
                JSONObject json = new JSONObject(returnedJson);
                if (Utils.Errors.checkAPIError(json)){
                    if (responseObserver != null)
                    {
                        Bundle answer = new Bundle();
                        answer.putString(BUNDLE_KEY_ERROR_MSG, Utils.Errors.getClientMessageFromErrorCode(this, json.getJSONObject("info").getString("return_code")));
                        responseObserver.send(Activity.RESULT_CANCELED, answer);
                    }
                    throw new NetworkErrorException(Utils.Errors.ERROR_API_GENERIC);
                }
                else {
                    JSONArray bugsData = json.getJSONObject("data").getJSONArray("array");
                    ArrayList<Long> existingComments = new ArrayList<>();
                    if (bugsData.length() != 0){
                        for (int i = 0; i < bugsData.length(); ++i) {
                            ContentValues currentValue = new ContentValues();
                            JSONObject bug = bugsData.getJSONObject(i);

                            String grappboxCID = bug.getJSONObject("creator").getString("id");
                            Cursor creatorId = getContentResolver().query(GrappboxContract.UserEntry.CONTENT_URI, new String[] {GrappboxContract.UserEntry._ID}, GrappboxContract.UserEntry.COLUMN_GRAPPBOX_ID + "=?", new String[]{grappboxCID}, null);

                            if (creatorId == null || !creatorId.moveToFirst())
                            {
                                GrappboxJustInTimeService.handleUserDetailSync(this, grappboxCID);
                                creatorId = getContentResolver().query(GrappboxContract.UserEntry.CONTENT_URI, new String[] {GrappboxContract.UserEntry._ID}, GrappboxContract.UserEntry.COLUMN_GRAPPBOX_ID + "=?", new String[]{grappboxCID}, null);
                                if (creatorId == null || !creatorId.moveToFirst()){
                                    continue;
                                }
                            }
                            currentValue.put(GrappboxContract.BugEntry.COLUMN_GRAPPBOX_ID, bug.getString("id"));
                            currentValue.put(GrappboxContract.BugEntry.COLUMN_LOCAL_CREATOR_ID, creatorId.getLong(0));
                            creatorId.close();
                            currentValue.put(GrappboxContract.BugEntry.COLUMN_LOCAL_PROJECT_ID, localPID);
                            currentValue.put(GrappboxContract.BugEntry.COLUMN_TITLE, "");
                            currentValue.put(GrappboxContract.BugEntry.COLUMN_DESCRIPTION, bug.getString("comment"));
                            currentValue.put(GrappboxContract.BugEntry.COLUMN_LOCAL_PARENT_ID, bugId);
                            String last_edited = Utils.Date.getDateFromGrappboxAPIToUTC(bug.getString(bug.isNull("editedAt") ? "createdAt" : "editedAt"));
                            currentValue.put(GrappboxContract.BugEntry.COLUMN_DATE_LAST_EDITED_UTC, last_edited);
                            if (!bug.isNull("deletedAt")){
                                currentValue.put(GrappboxContract.BugEntry.COLUMN_DATE_DELETED_UTC, Utils.Date.getDateFromGrappboxAPIToUTC(bug.getString("deletedAt")));
                            }
                            Uri commentUri = getContentResolver().insert(GrappboxContract.BugEntry.CONTENT_URI, currentValue);
                            if (commentUri == null)
                                continue;
                            long commentId = Long.parseLong(commentUri.getLastPathSegment());
                            existingComments.add(commentId);
                        }
                    }
                    String selection = GrappboxContract.BugEntry.COLUMN_LOCAL_PARENT_ID + "="+bugId+ (existingComments.size() > 0 ? " AND " + GrappboxContract.BugEntry._ID + " NOT IN (" : "");
                    if (existingComments.size() > 0){
                        boolean isFirst = true;
                        for (Long commentId : existingComments){
                            selection += isFirst ? commentId.toString() : ", " + commentId.toString();
                            isFirst = false;
                        }
                        selection += ")";
                    }
                    getContentResolver().delete(GrappboxContract.BugEntry.CONTENT_URI, selection, null);
                }
            }
        } catch (IOException | JSONException | ParseException | NetworkErrorException e) {
            e.printStackTrace();
        } finally {
            grappboxBugId.close();
            grappboxProjectId.close();
            if (connection != null)
                connection.disconnect();
            if (responseObserver != null){
                responseObserver.send(Activity.RESULT_OK, null);
            }
        }
    }

    private void handleBugCreate(boolean isEditMode, long bugOrProjectID, String title, String description, boolean isClientOrigin, ResultReceiver responseObserver){
        String apiToken = Utils.Account.getAuthTokenService(this, null);
        HttpURLConnection connection = null;
        String returnedJson;
        Cursor bug = null;
        Cursor project = null;

        try {
            if (apiToken == null)
                throw new NetworkErrorException(Utils.Errors.ERROR_INVALID_TOKEN);

            bug = getContentResolver().query(GrappboxContract.BugEntry.CONTENT_URI, new String[]{GrappboxContract.BugEntry.COLUMN_GRAPPBOX_ID}, GrappboxContract.BugEntry._ID + "=?", new String[]{String.valueOf(bugOrProjectID)}, null);
            project = getContentResolver().query(GrappboxContract.ProjectEntry.CONTENT_URI, new String[]{GrappboxContract.ProjectEntry.COLUMN_GRAPPBOX_ID}, GrappboxContract.ProjectEntry._ID + "=?", new String[]{String.valueOf(bugOrProjectID)}, null);
            if ((isEditMode && (bug == null || !bug.moveToFirst())) || (!isEditMode && (project == null || !project.moveToFirst())))
                throw new NetworkErrorException(Utils.Errors.ERROR_INVALID_ID);
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/bugtracker/ticket"+(isEditMode ? "/" + bug.getString(0) : ""));
            JSONObject json = new JSONObject();
            JSONObject data = new JSONObject();
            //TODO : Optimize bugtracker to use this request with full features on edition and creation
            if (isEditMode){
                data.put("addTags", new JSONArray());
                data.put("removeTags", new JSONArray());
                data.put("addUsers", new JSONArray());
                data.put("removeUsers", new JSONArray());
            }else{
                data.put("projectId", project.getString(0));
                data.put("tags", new JSONArray());
                data.put("users", new JSONArray());
            }
            data.put("title", title);
            data.put("description", description);
            data.put("clientOrigin", isClientOrigin);
            json.put("data", data);
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestProperty("Authorization", apiToken);
            connection.setRequestMethod(isEditMode ? "PUT" : "POST");
            Utils.JSON.sendJsonOverConnection(connection, json);
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson == null || returnedJson.isEmpty()){
                throw new NetworkErrorException("Returned JSON is empty");
            } else {
                json = new JSONObject(returnedJson);
                if (Utils.Errors.checkAPIError(json)){
                    if (responseObserver != null){
                        Bundle answer = new Bundle();
                        answer.putString(BUNDLE_KEY_ERROR_MSG, Utils.Errors.getClientMessageFromErrorCode(this, json.getJSONObject("info").getString("return_code")));
                        responseObserver.send(Activity.RESULT_CANCELED, answer);
                    }
                } else {
                    data = json.getJSONObject("data");
                    ContentValues values = new ContentValues();
                    values.put(GrappboxContract.BugEntry.COLUMN_TITLE, title);
                    values.putNull(GrappboxContract.BugEntry.COLUMN_DATE_DELETED_UTC);
                    values.put(GrappboxContract.BugEntry.COLUMN_DATE_LAST_EDITED_UTC, Utils.Date.getDateFromGrappboxAPIToUTC(data.getString("createdAt")));
                    values.put(GrappboxContract.BugEntry.COLUMN_DESCRIPTION, data.getString("description"));
                    values.putNull(GrappboxContract.BugEntry.COLUMN_LOCAL_PARENT_ID);

                    String grappboxCID = data.getJSONObject("creator").getString("id");
                    Cursor creatorId = getContentResolver().query(GrappboxContract.UserEntry.CONTENT_URI, new String[] {GrappboxContract.UserEntry._ID}, GrappboxContract.UserEntry.COLUMN_GRAPPBOX_ID + "=?", new String[]{grappboxCID}, null);

                    if (creatorId == null || !creatorId.moveToFirst())
                    {
                        GrappboxJustInTimeService.handleUserDetailSync(this, grappboxCID);
                        creatorId = getContentResolver().query(GrappboxContract.UserEntry.CONTENT_URI, new String[] {GrappboxContract.UserEntry._ID}, GrappboxContract.UserEntry.COLUMN_GRAPPBOX_ID + "=?", new String[]{grappboxCID}, null);
                        if (creatorId == null || !creatorId.moveToFirst()){
                            throw new UnknownError();
                        }
                    }
                    if (!isEditMode)
                        project.close();
                    project = getContentResolver().query(GrappboxContract.ProjectEntry.CONTENT_URI, new String[]{GrappboxContract.ProjectEntry._ID}, GrappboxContract.ProjectEntry.COLUMN_GRAPPBOX_ID+"=?", new String[]{data.getString("projectId")}, null);
                    if (project == null || !project.moveToFirst())
                        throw new NetworkErrorException(Utils.Errors.ERROR_INVALID_ID);
                    values.put(GrappboxContract.BugEntry.COLUMN_GRAPPBOX_ID, data.getString("id"));
                    values.put(GrappboxContract.BugEntry.COLUMN_LOCAL_CREATOR_ID, creatorId.getLong(0));
                    values.put(GrappboxContract.BugEntry.COLUMN_LOCAL_PROJECT_ID, project.getLong(0));
                    Uri res = getContentResolver().insert(GrappboxContract.BugEntry.CONTENT_URI, values);
                    if (res == null)
                        throw new SQLException(Utils.Errors.ERROR_SQL_INSERT_FAILED);
                    long id = Long.parseLong(res.getLastPathSegment());
                    creatorId.close();
                    Cursor user = getContentResolver().query(GrappboxContract.BugEntry.CONTENT_URI, null, GrappboxContract.BugEntry._ID+"=?", new String[]{String.valueOf(id)}, null);
                    if (user != null){
                        if (user.moveToFirst()){
                            Bundle answer = new Bundle();
                            BugModel model = new BugModel(this, user);
                            model.setProjectID(user.getLong(user.getColumnIndex(GrappboxContract.BugEntry.COLUMN_LOCAL_PROJECT_ID)));
                            answer.putParcelable(BugReceiver.EXTRA_BUG_MODEL, model);
                            responseObserver.send(Activity.RESULT_OK, answer);
                        }
                        user.close();
                    }
                }
            }
        } catch (IOException | JSONException | NetworkErrorException | ParseException e) {
            e.printStackTrace();
        } finally {
            if (connection != null)
                connection.disconnect();
            if (bug != null)
                bug.close();
            if (project != null)
                project.close();
        }
    }

    private void handleBugReopenComment(long bugId, ResultReceiver responseObserver){
        String apiToken = Utils.Account.getAuthTokenService(this, null);
        HttpURLConnection connection = null;
        String returnedJson;
        Cursor bug = null;
        Cursor user = null;

        try {
            if (apiToken == null)
                throw new NetworkErrorException(Utils.Errors.ERROR_INVALID_TOKEN);

            bug = getContentResolver().query(GrappboxContract.BugEntry.CONTENT_URI, new String[]{GrappboxContract.BugEntry.COLUMN_GRAPPBOX_ID, GrappboxContract.BugEntry.COLUMN_TITLE}, GrappboxContract.BugEntry._ID + "=?", new String[]{String.valueOf(bugId)}, null);
            user = getContentResolver().query(GrappboxContract.UserEntry.CONTENT_URI, new String[]{GrappboxContract.BugEntry._ID}, GrappboxContract.UserEntry.COLUMN_CONTACT_EMAIL+"=?", new String[]{Session.getInstance(this).getCurrentAccount().name}, null);
            if (bug == null || !bug.moveToFirst() || user == null || !user.moveToFirst())
                throw new NetworkErrorException(Utils.Errors.ERROR_INVALID_ID);
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/bugtracker/ticket/reopen/"+bug.getString(0));

            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestProperty("Authorization", apiToken);
            connection.setRequestMethod("GET");
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson == null || returnedJson.isEmpty()){
                throw new NetworkErrorException(Utils.Errors.ERROR_API_ANSWER_EMPTY);
            } else {
                JSONObject json = new JSONObject(returnedJson);
                if (Utils.Errors.checkAPIError(json)){
                    if (responseObserver != null){
                        Bundle answer = new Bundle();
                        answer.putString(BUNDLE_KEY_ERROR_MSG, Utils.Errors.getClientMessageFromErrorCode(this, json.getJSONObject("info").getString("return_code")));
                        responseObserver.send(Activity.RESULT_CANCELED, answer);
                    }
                    throw new NetworkErrorException(Utils.Errors.ERROR_API_GENERIC);
                } else {
                    ContentValues values = new ContentValues();
                    values.put(GrappboxContract.BugEntry.COLUMN_GRAPPBOX_ID, bug.getString(0));
                    values.put(GrappboxContract.BugEntry.COLUMN_LOCAL_CREATOR_ID, user.getLong(0));
                    values.putNull(GrappboxContract.BugEntry.COLUMN_DATE_DELETED_UTC);
                    values.put(GrappboxContract.BugEntry.COLUMN_DATE_LAST_EDITED_UTC, Utils.Date.nowUTC());
                    getContentResolver().insert(GrappboxContract.BugEntry.CONTENT_URI, values);
                }
            }
        } catch (IOException | JSONException | NetworkErrorException | ParseException e) {
            e.printStackTrace();
        } finally {
            if (connection != null)
                connection.disconnect();
            if (bug != null)
                bug.close();
            if (user != null)
                user.close();
        }
    }

    private void handleBugClose(long bugID, ResultReceiver responseObserver){
        handleBugCloseComment(bugID, responseObserver, true);
    }

    private void handleBugCloseComment(long commentID, ResultReceiver responseObserver, boolean... keepDB){
        String apiToken = Utils.Account.getAuthTokenService(this, null);
        boolean isBug = keepDB != null && keepDB.length > 0 && keepDB[0];
        HttpURLConnection connection = null;
        String returnedJson;
        Cursor bug = null;

        try {
            if (apiToken == null)
                throw new NetworkErrorException(Utils.Errors.ERROR_INVALID_TOKEN);

            bug = getContentResolver().query(GrappboxContract.BugEntry.CONTENT_URI, new String[]{GrappboxContract.BugEntry.COLUMN_GRAPPBOX_ID}, GrappboxContract.BugEntry._ID + "=?", new String[]{String.valueOf(commentID)}, null);
            if (bug == null || !bug.moveToFirst())
                throw new NetworkErrorException(Utils.Errors.ERROR_INVALID_ID);
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/bugtracker/" + (isBug ? "ticket/closed/" : "comment/")+bug.getString(0));
            JSONObject json = new JSONObject();

            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestMethod("DELETE");
            Utils.JSON.sendJsonOverConnection(connection, json);
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson == null || returnedJson.isEmpty()){
                throw new NetworkErrorException(Utils.Errors.ERROR_API_ANSWER_EMPTY);
            } else {
                json = new JSONObject(returnedJson);
                if (Utils.Errors.checkAPIError(json)){
                    if (responseObserver != null){
                        Bundle answer = new Bundle();
                        answer.putString(BUNDLE_KEY_ERROR_MSG, Utils.Errors.getClientMessageFromErrorCode(this, json.getJSONObject("info").getString("return_code")));
                        responseObserver.send(Activity.RESULT_CANCELED, answer);
                    }
                    throw new NetworkErrorException(Utils.Errors.ERROR_API_GENERIC);
                } else {
                    if (!isBug)
                        getContentResolver().delete(GrappboxContract.BugEntry.CONTENT_URI, GrappboxContract.BugEntry._ID+"=?", new String[]{String.valueOf(commentID)});
                    else{
                        ContentValues values = new ContentValues();
                        values.put(GrappboxContract.BugEntry.COLUMN_GRAPPBOX_ID, bug.getString(0));
                        values.put(GrappboxContract.BugEntry.COLUMN_DATE_DELETED_UTC, Utils.Date.nowUTC());
                        getContentResolver().insert(GrappboxContract.BugEntry.CONTENT_URI, values);
                    }

                }
            }
        } catch (IOException | JSONException | NetworkErrorException | ParseException e) {
            e.printStackTrace();
        } finally {
            if (connection != null)
                connection.disconnect();
            if (bug != null)
                bug.close();
        }
    }

    private void handleBugEditComment(long projectId, long bugID, long commentID, String message, ResultReceiver responseObserver) {
        String apiToken = Utils.Account.getAuthTokenService(this, null);
        HttpURLConnection connection = null;
        String returnedJson;
        Cursor project = null;
        Cursor bug = null;

        try {
            if (projectId == -1 || apiToken == null)
                throw new NetworkErrorException(Utils.Errors.ERROR_INVALID_ID + " || " + Utils.Errors.ERROR_INVALID_TOKEN);

            project = getContentResolver().query(GrappboxContract.ProjectEntry.CONTENT_URI, new String[]{GrappboxContract.ProjectEntry.COLUMN_GRAPPBOX_ID}, GrappboxContract.ProjectEntry._ID + "=?", new String[]{String.valueOf(projectId)}, null);
            bug = getContentResolver().query(GrappboxContract.BugEntry.CONTENT_URI, new String[]{GrappboxContract.BugEntry.COLUMN_GRAPPBOX_ID}, GrappboxContract.BugEntry._ID + "=?", new String[]{String.valueOf(commentID)}, null);
            if (project == null || !project.moveToFirst() || bug == null || !bug.moveToFirst())
                throw new NetworkErrorException(Utils.Errors.ERROR_INVALID_ID);
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/bugtracker/comment/"+bug.getString(0));
            JSONObject json = new JSONObject();
            JSONObject data = new JSONObject();

            data.put("comment", message);
            json.put("data", data);
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestProperty("Authorization", apiToken);
            connection.setRequestMethod("PUT");
            Utils.JSON.sendJsonOverConnection(connection, json);
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson == null || returnedJson.isEmpty()){
                throw new NetworkErrorException(Utils.Errors.ERROR_API_ANSWER_EMPTY);
            } else {
                json = new JSONObject(returnedJson);
                if (Utils.Errors.checkAPIError(json)){
                    if (responseObserver != null){
                        Bundle answer = new Bundle();
                        answer.putString(BUNDLE_KEY_ERROR_MSG, Utils.Errors.getClientMessageFromErrorCode(this, json.getJSONObject("info").getString("return_code")));
                        responseObserver.send(Activity.RESULT_CANCELED, answer);
                    }
                    throw new NetworkErrorException(Utils.Errors.ERROR_API_GENERIC);
                } else {
                    data = json.getJSONObject("data");
                    ContentValues values = new ContentValues();
                    values.put(GrappboxContract.BugEntry.COLUMN_TITLE, "");
                    values.putNull(GrappboxContract.BugEntry.COLUMN_DATE_DELETED_UTC);
                    values.put(GrappboxContract.BugEntry.COLUMN_DATE_LAST_EDITED_UTC, Utils.Date.getDateFromGrappboxAPIToUTC(data.isNull("editedAt") ? data.getString("createdAt") : data.getString("editedAt")));
                    values.put(GrappboxContract.BugEntry.COLUMN_DESCRIPTION, data.getString("comment"));
                    values.put(GrappboxContract.BugEntry.COLUMN_LOCAL_PARENT_ID, bugID);

                    String grappboxCID = data.getJSONObject("creator").getString("id");
                    Cursor creatorId = getContentResolver().query(GrappboxContract.UserEntry.CONTENT_URI, new String[] {GrappboxContract.UserEntry._ID}, GrappboxContract.UserEntry.COLUMN_GRAPPBOX_ID + "=?", new String[]{grappboxCID}, null);

                    if (creatorId == null || !creatorId.moveToFirst())
                    {
                        GrappboxJustInTimeService.handleUserDetailSync(this, grappboxCID);
                        creatorId = getContentResolver().query(GrappboxContract.UserEntry.CONTENT_URI, new String[] {GrappboxContract.UserEntry._ID}, GrappboxContract.UserEntry.COLUMN_GRAPPBOX_ID + "=?", new String[]{grappboxCID}, null);
                        if (creatorId == null || !creatorId.moveToFirst())
                            throw new UnknownError();
                    }
                    values.put(GrappboxContract.BugEntry.COLUMN_GRAPPBOX_ID, data.getString("id"));
                    values.put(GrappboxContract.BugEntry.COLUMN_LOCAL_CREATOR_ID, creatorId.getLong(0));
                    values.put(GrappboxContract.BugEntry.COLUMN_LOCAL_PROJECT_ID, projectId);
                    getContentResolver().insert(GrappboxContract.BugEntry.CONTENT_URI, values);
                    creatorId.close();
                }
            }
        } catch (IOException | JSONException | NetworkErrorException | ParseException e) {
            e.printStackTrace();
        } finally {
            if (connection != null)
                connection.disconnect();
            if (project != null)
                project.close();
            if (bug != null)
                bug.close();
        }
    }

    private void handleBugPostComment(long projectId, long bugID, String message, ResultReceiver responseObserver) {
        String apiToken = Utils.Account.getAuthTokenService(this, null);
        HttpURLConnection connection = null;
        String returnedJson;
        Cursor project = null;
        Cursor bug = null;

        try {
            if (projectId == -1 || apiToken == null)
                throw new NetworkErrorException(Utils.Errors.ERROR_INVALID_ID + " || " + Utils.Errors.ERROR_INVALID_TOKEN);

            project = getContentResolver().query(GrappboxContract.ProjectEntry.CONTENT_URI, new String[]{GrappboxContract.ProjectEntry.COLUMN_GRAPPBOX_ID}, GrappboxContract.ProjectEntry._ID + "=?", new String[]{String.valueOf(projectId)}, null);
            bug = getContentResolver().query(GrappboxContract.BugEntry.CONTENT_URI, new String[]{GrappboxContract.BugEntry.COLUMN_GRAPPBOX_ID}, GrappboxContract.BugEntry._ID + "=?", new String[]{String.valueOf(bugID)}, null);
            if (project == null || !project.moveToFirst() || bug == null || !bug.moveToFirst())
                throw new NetworkErrorException(Utils.Errors.ERROR_INVALID_ID);
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/bugtracker/comment");
            JSONObject json = new JSONObject();
            JSONObject data = new JSONObject();

            data.put("comment", message);
            data.put("parentId", bug.getString(0));
            json.put("data", data);
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestProperty("Authorization", apiToken);
            connection.setRequestMethod("POST");
            Utils.JSON.sendJsonOverConnection(connection, json);
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson == null || returnedJson.isEmpty()){
                throw new NetworkErrorException(Utils.Errors.ERROR_API_ANSWER_EMPTY);
            } else {
                json = new JSONObject(returnedJson);
                if (Utils.Errors.checkAPIError(json)){
                    if (responseObserver != null){
                        Bundle answer = new Bundle();
                        answer.putString(BUNDLE_KEY_ERROR_MSG, Utils.Errors.getClientMessageFromErrorCode(this, json.getJSONObject("info").getString("return_code")));
                        responseObserver.send(Activity.RESULT_CANCELED, answer);
                    }
                    throw new OperationApplicationException(Utils.Errors.ERROR_API_GENERIC);
                } else {
                    data = json.getJSONObject("data");
                    ContentValues values = new ContentValues();
                    values.put(GrappboxContract.BugEntry.COLUMN_TITLE, "");
                    values.putNull(GrappboxContract.BugEntry.COLUMN_DATE_DELETED_UTC);
                    values.put(GrappboxContract.BugEntry.COLUMN_DATE_LAST_EDITED_UTC, Utils.Date.getDateFromGrappboxAPIToUTC(data.isNull("editedAt") ? data.getString("createdAt") : data.getString("editedAt")));
                    values.put(GrappboxContract.BugEntry.COLUMN_DESCRIPTION, data.getString("comment"));
                    values.put(GrappboxContract.BugEntry.COLUMN_LOCAL_PARENT_ID, bugID);

                    String grappboxCID = data.getJSONObject("creator").getString("id");
                    Cursor creatorId = getContentResolver().query(GrappboxContract.UserEntry.CONTENT_URI, new String[] {GrappboxContract.UserEntry._ID}, GrappboxContract.UserEntry.COLUMN_GRAPPBOX_ID + "=?", new String[]{grappboxCID}, null);

                    if (creatorId == null || !creatorId.moveToFirst())
                    {
                        GrappboxJustInTimeService.handleUserDetailSync(this, grappboxCID);
                        creatorId = getContentResolver().query(GrappboxContract.UserEntry.CONTENT_URI, new String[] {GrappboxContract.UserEntry._ID}, GrappboxContract.UserEntry.COLUMN_GRAPPBOX_ID + "=?", new String[]{grappboxCID}, null);
                        if (creatorId == null || !creatorId.moveToFirst()){
                            throw new UnknownError();
                        }
                    }
                    values.put(GrappboxContract.BugEntry.COLUMN_GRAPPBOX_ID, data.getString("id"));
                    values.put(GrappboxContract.BugEntry.COLUMN_LOCAL_CREATOR_ID, creatorId.getLong(0));
                    values.put(GrappboxContract.BugEntry.COLUMN_LOCAL_PROJECT_ID, projectId);
                    getContentResolver().insert(GrappboxContract.BugEntry.CONTENT_URI, values);
                    creatorId.close();
                }
            }
        } catch (IOException | JSONException | NetworkErrorException | ParseException | OperationApplicationException e) {
            e.printStackTrace();
        } finally {
            if (connection != null)
                connection.disconnect();
            if (project != null)
                project.close();
            if (bug != null)
                bug.close();
        }
    }

    private void handleTagSync(long projectId) {
        String apiToken = Utils.Account.getAuthTokenService(this, null);
        HttpURLConnection connection = null;
        String returnedJson;

        Cursor project = null;

        try {
            if (apiToken == null)
                throw new NetworkErrorException(Utils.Errors.ERROR_INVALID_TOKEN);

            project = getContentResolver().query(GrappboxContract.ProjectEntry.CONTENT_URI, new String[]{GrappboxContract.ProjectEntry.COLUMN_GRAPPBOX_ID}, GrappboxContract.ProjectEntry._ID + "=?", new String[]{String.valueOf(projectId)}, null);
            if (project == null || !project.moveToFirst())
                throw new IllegalArgumentException(Utils.Errors.ERROR_INVALID_ID);
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/bugtracker/project/tags/"+project.getString(0));
            JSONObject json;
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestProperty("Authorization", apiToken);
            connection.setRequestMethod("GET");
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson == null || returnedJson.isEmpty()){
                throw new NetworkErrorException(Utils.Errors.ERROR_API_ANSWER_EMPTY);
            } else {
                json = new JSONObject(returnedJson);
                if (Utils.Errors.checkAPIError(json)){
                    throw new NetworkErrorException(Utils.Errors.ERROR_API_GENERIC);
                } else {
                    JSONArray data = json.getJSONObject("data").getJSONArray("array");

                    for (int i = 0; i < data.length(); ++i){
                        JSONObject current = data.getJSONObject(i);
                        ContentValues value = new ContentValues();
                        value.put(GrappboxContract.BugtrackerTagEntry.COLUMN_GRAPPBOX_ID, current.getString("id"));
                        value.put(GrappboxContract.BugtrackerTagEntry.COLUMN_LOCAL_PROJECT_ID, projectId);
                        value.put(GrappboxContract.BugtrackerTagEntry.COLUMN_NAME, current.getString("name"));
                        value.put(GrappboxContract.BugtrackerTagEntry.COLUMN_COLOR, current.getString("color").startsWith("#") ? current.getString("color") : "#"+current.getString("color"));
                        getContentResolver().insert(GrappboxContract.BugtrackerTagEntry.CONTENT_URI, value);
                    }
                }
            }
        } catch (IOException | JSONException | NetworkErrorException e) {
            e.printStackTrace();
        } finally {
            if (connection != null)
                connection.disconnect();
            if (project != null)
                project.close();
        }
    }

    private void handleCreateTag(long projectId, long bugId, String tagname, String color, ResultReceiver responseObserver) {
        String apiToken = Utils.Account.getAuthTokenService(this, null);
        HttpURLConnection connection = null;
        String returnedJson;

        Cursor project = null;

        try {
            if (apiToken == null)
                throw new NetworkErrorException(Utils.Errors.ERROR_INVALID_TOKEN);

            project = getContentResolver().query(GrappboxContract.ProjectEntry.CONTENT_URI, new String[]{GrappboxContract.ProjectEntry.COLUMN_GRAPPBOX_ID}, GrappboxContract.ProjectEntry._ID + "=?", new String[]{String.valueOf(projectId)}, null);
            if (project == null || !project.moveToFirst())
                throw new IllegalArgumentException(Utils.Errors.ERROR_INVALID_ID);
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/bugtracker/tag");
            JSONObject json = new JSONObject();
            JSONObject data = new JSONObject();

            data.put("projectId", project.getString(0));
            data.put("name", tagname);
            data.put("color", (color.startsWith("#") ? color.substring(1) : color));
            json.put("data", data);
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestProperty("Authorization", apiToken);
            connection.setRequestMethod("POST");
            Utils.JSON.sendJsonOverConnection(connection, json);
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson == null || returnedJson.isEmpty()){
                throw new NetworkErrorException(Utils.Errors.ERROR_API_ANSWER_EMPTY);
            } else {
                json = new JSONObject(returnedJson);
                if (Utils.Errors.checkAPIError(json)){
                    if (responseObserver != null){
                        Bundle answer = new Bundle();
                        answer.putString(BUNDLE_KEY_ERROR_MSG, Utils.Errors.getClientMessageFromErrorCode(this, json.getJSONObject("info").getString("return_code")));
                        responseObserver.send(Activity.RESULT_CANCELED, answer);
                    }
                    throw new OperationApplicationException(Utils.Errors.ERROR_API_GENERIC);
                } else {
                    ContentValues value = new ContentValues();
                    value.put(GrappboxContract.BugtrackerTagEntry.COLUMN_GRAPPBOX_ID, json.getJSONObject("data").getString("id"));
                    value.put(GrappboxContract.BugtrackerTagEntry.COLUMN_LOCAL_PROJECT_ID, projectId);
                    value.put(GrappboxContract.BugtrackerTagEntry.COLUMN_NAME, tagname);
                    value.put(GrappboxContract.BugtrackerTagEntry.COLUMN_COLOR, color);
                    Uri res = getContentResolver().insert(GrappboxContract.BugtrackerTagEntry.CONTENT_URI, value);
                    if (res == null)
                        throw new SQLException(Utils.Errors.ERROR_SQL_INSERT_FAILED);
                    long id = Long.parseLong(res.getLastPathSegment());
                    handleEditBugTag(bugId, id, responseObserver);
                }
            }
        } catch (IOException | JSONException | NetworkErrorException | OperationApplicationException e) {
            e.printStackTrace();
        } finally {
            if (connection != null)
                connection.disconnect();
            if (project != null)
                project.close();
        }
    }

    private void handleBugSetParticipant(long bugId, List<Long> toAdd, List<Long> toDel, ResultReceiver responseObserver) {
        String apiToken = Utils.Account.getAuthTokenService(this, null);
        HttpURLConnection connection = null;
        String returnedJson;
        Cursor bug = null, userToAdd = null, userToDel = null;

        try {
            if (apiToken == null)
                throw new NetworkErrorException(Utils.Errors.ERROR_INVALID_TOKEN);
            bug = getContentResolver().query(GrappboxContract.BugEntry.CONTENT_URI, new String[]{GrappboxContract.BugEntry.COLUMN_GRAPPBOX_ID}, GrappboxContract.BugEntry._ID+"=?", new String[]{String.valueOf(bugId)}, null);
            String userAddSelection = "";
            String userDelSelection = "";
            for (Long add : toAdd){
                userAddSelection += userAddSelection.isEmpty() ? "(" + add : "," + add;
            }
            if (!userAddSelection.isEmpty()){
                userAddSelection += ")";
                userToAdd = getContentResolver().query(GrappboxContract.UserEntry.CONTENT_URI, new String[]{GrappboxContract.UserEntry.COLUMN_GRAPPBOX_ID}, GrappboxContract.UserEntry._ID+" IN " + userAddSelection, null, null);
            }
            for (Long del : toDel){
                userDelSelection += userDelSelection.isEmpty() ? "(" + del : "," + del;
            }
            if (!userDelSelection.isEmpty()){
                userDelSelection += ")";
                userToDel = getContentResolver().query(GrappboxContract.UserEntry.CONTENT_URI, new String[]{GrappboxContract.UserEntry.COLUMN_GRAPPBOX_ID}, GrappboxContract.UserEntry._ID+" IN " + userDelSelection, null, null);
            }
            if (bug == null || !bug.moveToFirst())
                throw new IllegalArgumentException(Utils.Errors.ERROR_INVALID_ID);
            JSONArray toAddObj = new JSONArray();
            if (userToAdd != null && userToAdd.moveToFirst()){
                do{
                    toAddObj.put(userToAdd.getString(0));
                } while (userToAdd.moveToNext());
            }
            JSONArray toDelObj = new JSONArray();
            if (userToDel != null && userToDel.moveToFirst()){
                do{
                    toDelObj.put(userToDel.getString(0));
                } while (userToDel.moveToNext());
            }
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/bugtracker/users/"+bug.getString(0));
            JSONObject json = new JSONObject(), data = new JSONObject();

            data.put("toAdd", toAddObj);
            data.put("toRemove", toDelObj);
            json.put("data", data);
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestProperty("Authorization", apiToken);
            connection.setRequestMethod("PUT");
            Utils.JSON.sendJsonOverConnection(connection, json);
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson == null || returnedJson.isEmpty()){
                throw new NetworkErrorException(Utils.Errors.ERROR_API_ANSWER_EMPTY);
            } else {
                json = new JSONObject(returnedJson);
                if (Utils.Errors.checkAPIError(json)){
                    if (responseObserver != null){
                        Bundle answer = new Bundle();
                        answer.putString(BUNDLE_KEY_ERROR_MSG, Utils.Errors.getClientMessageFromErrorCode(this, json.getJSONObject("info").getString("return_code")));
                        responseObserver.send(Activity.RESULT_CANCELED, answer);
                    }
                } else {
                    for (Long del : toDel){
                        getContentResolver().delete(GrappboxContract.BugAssignationEntry.CONTENT_URI, GrappboxContract.BugAssignationEntry.COLUMN_LOCAL_USER_ID+"=?", new String[]{String.valueOf(del)});
                    }
                    for (Long add : toAdd){
                        ContentValues value = new ContentValues();
                        value.put(GrappboxContract.BugAssignationEntry.COLUMN_LOCAL_BUG_ID, bugId);
                        value.put(GrappboxContract.BugAssignationEntry.COLUMN_LOCAL_USER_ID, add);
                        getContentResolver().insert(GrappboxContract.BugAssignationEntry.CONTENT_URI, value);
                    }
                }
            }
        } catch (IOException | JSONException | NetworkErrorException e) {
            e.printStackTrace();
        } finally {
            if (connection != null)
                connection.disconnect();
            if (bug != null)
                bug.close();
            if (userToAdd != null)
                userToAdd.close();
            if (userToDel != null)
                userToDel.close();
        }
    }

    private void handleRemoveBugTag(long bugId, long tagId, ResultReceiver responseObserver) {
        String apiToken = Utils.Account.getAuthTokenService(this, null);
        HttpURLConnection connection = null;
        String returnedJson;
        Cursor bug = null, tag = null;

        try {
            if (apiToken == null)
                throw new NetworkErrorException(Utils.Errors.ERROR_INVALID_TOKEN);
            bug = getContentResolver().query(GrappboxContract.BugEntry.CONTENT_URI, new String[]{GrappboxContract.BugEntry.TABLE_NAME + "." + GrappboxContract.BugEntry.COLUMN_GRAPPBOX_ID}, GrappboxContract.BugEntry.TABLE_NAME + "." + GrappboxContract.BugEntry._ID+"=?", new String[]{String.valueOf(bugId)}, null);
            tag = getContentResolver().query(GrappboxContract.BugtrackerTagEntry.CONTENT_URI, new String[]{GrappboxContract.BugtrackerTagEntry.TABLE_NAME + "." + GrappboxContract.BugtrackerTagEntry.COLUMN_GRAPPBOX_ID}, GrappboxContract.BugtrackerTagEntry.TABLE_NAME + "." + GrappboxContract.BugtrackerTagEntry._ID+"=?", new String[]{String.valueOf(tagId)}, null);
            if (bug == null || tag == null || !bug.moveToFirst() || !tag.moveToFirst())
                throw new IllegalArgumentException(Utils.Errors.ERROR_INVALID_ID);
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/bugtracker/tag/remove/"+bug.getString(0)+"/"+tag.getString(0));
            JSONObject json;

            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestProperty("Authorization", apiToken);
            connection.setRequestMethod("DELETE");
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson == null || returnedJson.isEmpty()){
                throw new NetworkErrorException(Utils.Errors.ERROR_API_ANSWER_EMPTY);
            } else {
                json = new JSONObject(returnedJson);
                if (Utils.Errors.checkAPIError(json)){
                    if (responseObserver != null){
                        Bundle answer = new Bundle();
                        answer.putString(BUNDLE_KEY_ERROR_MSG, Utils.Errors.getClientMessageFromErrorCode(this, json.getJSONObject("info").getString("return_code")));
                        responseObserver.send(Activity.RESULT_CANCELED, answer);
                    }
                } else {
                    Cursor tagAssign = getContentResolver().query(GrappboxContract.BugTagEntry.CONTENT_URI, new String[]{GrappboxContract.BugTagEntry._ID}, GrappboxContract.BugTagEntry.COLUMN_LOCAL_BUG_ID+"=? AND " + GrappboxContract.BugTagEntry.COLUMN_LOCAL_TAG_ID+"=?", new String[]{String.valueOf(bugId), String.valueOf(tagId)}, null);
                    if (tagAssign != null && tagAssign.moveToFirst() && tagAssign.getCount() > 0){
                        getContentResolver().delete(GrappboxContract.BugTagEntry.CONTENT_URI, GrappboxContract.BugTagEntry.COLUMN_LOCAL_BUG_ID+"=? AND " + GrappboxContract.BugTagEntry.COLUMN_LOCAL_TAG_ID+"=?", new String[]{String.valueOf(bugId), String.valueOf(tagId)});
                        tagAssign.close();
                    }
                }
            }
        } catch (IOException | JSONException | NetworkErrorException e) {
            e.printStackTrace();
        } finally {
            if (connection != null)
                connection.disconnect();
            if (bug != null)
                bug.close();
            if (tag != null)
                tag.close();
        }
    }

    private void handleEditBugTag(long bugId, long tagId, ResultReceiver responseObserver) {
        String apiToken = Utils.Account.getAuthTokenService(this, null);
        HttpURLConnection connection = null;
        String returnedJson;
        Cursor bug = null, tag = null;

        try {
            if (apiToken == null)
                throw new NetworkErrorException(Utils.Errors.ERROR_INVALID_TOKEN);
            bug = getContentResolver().query(GrappboxContract.BugEntry.CONTENT_URI, new String[]{GrappboxContract.BugEntry.COLUMN_GRAPPBOX_ID}, GrappboxContract.BugEntry._ID+"=?", new String[]{String.valueOf(bugId)}, null);
            tag = getContentResolver().query(GrappboxContract.BugtrackerTagEntry.CONTENT_URI, new String[]{GrappboxContract.BugtrackerTagEntry.TABLE_NAME + "." + GrappboxContract.BugtrackerTagEntry.COLUMN_GRAPPBOX_ID}, GrappboxContract.BugtrackerTagEntry.TABLE_NAME + "." + GrappboxContract.BugtrackerTagEntry._ID+"=?", new String[]{String.valueOf(tagId)}, null);
            if (tag == null || !tag.moveToFirst() || bug == null || !bug.moveToFirst())
                throw new IllegalArgumentException(Utils.Errors.ERROR_INVALID_ID);
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/bugtracker/tag/assign/" + bug.getString(0));
            JSONObject json = new JSONObject();
            JSONObject data = new JSONObject();

            data.put("tagId", tag.getString(0));
            json.put("data", data);
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestProperty("Authorization", apiToken);
            connection.setRequestMethod("PUT");
            Utils.JSON.sendJsonOverConnection(connection, json);
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson == null || returnedJson.isEmpty()){
                throw new NetworkErrorException(Utils.Errors.ERROR_API_ANSWER_EMPTY);
            } else {
                json = new JSONObject(returnedJson);
                if (Utils.Errors.checkAPIError(json)){
                    if (responseObserver != null){
                        Bundle answer = new Bundle();
                        answer.putString(BUNDLE_KEY_ERROR_MSG, Utils.Errors.getClientMessageFromErrorCode(this, json.getJSONObject("info").getString("return_code")));
                        responseObserver.send(Activity.RESULT_CANCELED, answer);
                    }
                } else {
                    Cursor tagAssign = getContentResolver().query(GrappboxContract.BugTagEntry.CONTENT_URI, new String[]{GrappboxContract.BugTagEntry._ID}, GrappboxContract.BugTagEntry.COLUMN_LOCAL_BUG_ID+"=? AND " + GrappboxContract.BugTagEntry.COLUMN_LOCAL_TAG_ID+"=?", new String[]{String.valueOf(bugId), String.valueOf(tagId)}, null);
                    if (tagAssign == null || !tagAssign.moveToFirst() || tagAssign.getCount() == 0){
                        ContentValues value = new ContentValues();
                        value.put(GrappboxContract.BugTagEntry.COLUMN_LOCAL_BUG_ID, bugId);
                        value.put(GrappboxContract.BugTagEntry.COLUMN_LOCAL_TAG_ID, tagId);
                        getContentResolver().insert(GrappboxContract.BugTagEntry.CONTENT_URI, value);
                    } else {
                        tagAssign.close();
                    }
                }
            }
        } catch (IOException | JSONException | NetworkErrorException e) {
            e.printStackTrace();
        } finally {
            if (connection != null)
                connection.disconnect();
            if (bug != null)
                bug.close();
            if (tag != null)
                tag.close();
        }
    }

}
