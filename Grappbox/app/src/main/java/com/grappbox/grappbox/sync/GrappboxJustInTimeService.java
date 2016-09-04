package com.grappbox.grappbox.sync;

import android.accounts.AccountManager;
import android.app.Activity;
import android.app.IntentService;
import android.content.ContentValues;
import android.content.Intent;
import android.database.Cursor;
import android.net.Uri;
import android.os.Bundle;
import android.support.annotation.Nullable;
import android.support.v4.os.ResultReceiver;
import android.util.Log;

import com.grappbox.grappbox.BuildConfig;
import com.grappbox.grappbox.R;
import com.grappbox.grappbox.Utils;
import com.grappbox.grappbox.data.GrappboxContract;
import com.grappbox.grappbox.data.GrappboxContract.BugAssignationEntry;
import com.grappbox.grappbox.data.GrappboxContract.BugEntry;
import com.grappbox.grappbox.data.GrappboxContract.BugTagEntry;
import com.grappbox.grappbox.data.GrappboxContract.EventEntry;
import com.grappbox.grappbox.data.GrappboxContract.EventTypeEntry;
import com.grappbox.grappbox.data.GrappboxContract.TagEntry;
import com.grappbox.grappbox.data.GrappboxContract.TimelineMessageEntry;
import com.grappbox.grappbox.data.GrappboxContract.UserEntry;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;
import java.net.HttpURLConnection;
import java.net.MalformedURLException;
import java.net.ProtocolException;
import java.net.URL;
import java.text.ParseException;
import java.util.Date;
import java.util.Set;


/**
 * An {@link IntentService} subclass for handling asynchronous task requests in
 * a service on a separate handler thread.
 */
public class GrappboxJustInTimeService extends IntentService {
    private static final String LOG_TAG = GrappboxJustInTimeService.class.getSimpleName();

    public static final String ACTION_SYNC_USER_DETAIL = "com.grappbox.grappbox.sync.ACTION_SYNC_USER_DETAIL";
    public static final String ACTION_SYNC_BUGS = "com.grappbox.grappbox.sync.ACTION_SYNC_BUGS";
    public static final String ACTION_SYNC_TIMELINE_MESSAGES = "com.grappbox.grappbox.sync.ACTION_SYNC_TIMELINE_MESSAGES";
    public static final String ACTION_SYNC_NEXT_MEETINGS = "com.grappbox.grappbox.sync.ACTION_SYNC_NEXT_MEETINGS";
    public static final String ACTION_LOGIN = "com.grappbox.grappbox.sync.ACTION_LOGIN";

    public static final String EXTRA_API_TOKEN = "api_token";
    public static final String EXTRA_USER_ID = "uid";
    public static final String EXTRA_PROJECT_ID = "pid";
    public static final String EXTRA_OFFSET = "offset";
    public static final String EXTRA_LIMIT = "limit";
    public static final String EXTRA_TIMELINE_ID = "tid";
    public static final String EXTRA_RESPONSE_RECEIVER = "response_receiver";
    public static final String EXTRA_MAIL = "mail";
    public static final String EXTRA_CRYPTED_PASSWORD = "password";

    public static final String CATEGORY_GRAPPBOX_ID = "com.grappbox.grappbox.sync.CATEGORY_GRAPPBOX_ID";
    public static final String CATEGORY_LOCAL_ID = "com.grappbox.grappbox.sync.CATEGORY_LOCAL_ID";

    public GrappboxJustInTimeService() {
        super("GrappboxJustInTimeService");
    }

    @Override
    protected void onHandleIntent(Intent intent) {
        if (intent != null) {
            final String action = intent.getAction();
            if (ACTION_SYNC_USER_DETAIL.equals(action)){
                Set<String> categories = intent.getCategories();
                if (categories.size() == 0 || categories.contains(CATEGORY_LOCAL_ID))
                    handleUserDetailSync(intent.getStringExtra(EXTRA_API_TOKEN), intent.getLongExtra(EXTRA_USER_ID, -1));
                else
                    handleUserDetailSync(intent.getStringExtra(EXTRA_API_TOKEN), intent.getStringExtra(EXTRA_USER_ID));
            }
            else if (ACTION_SYNC_BUGS.equals(action))
                handleBugsSync(intent.getStringExtra(EXTRA_API_TOKEN), intent.getLongExtra(EXTRA_USER_ID, -1), intent.getLongExtra(EXTRA_PROJECT_ID, -1), intent.getIntExtra(EXTRA_OFFSET, 0), intent.getIntExtra(EXTRA_LIMIT, 50));
            else if (ACTION_SYNC_TIMELINE_MESSAGES.equals(action)) {
                handleTimelineMessagesSync(intent.getStringExtra(EXTRA_API_TOKEN), intent.getLongExtra(EXTRA_TIMELINE_ID, -1), intent.getIntExtra(EXTRA_OFFSET, 0), intent.getIntExtra(EXTRA_LIMIT, 50));
            }
            else if (ACTION_SYNC_NEXT_MEETINGS.equals(action)) {
                handleNextMeetingsSync(intent.getStringExtra(EXTRA_API_TOKEN), intent.getLongExtra(EXTRA_PROJECT_ID, -1));
            }
            else if (ACTION_LOGIN.equals(action)) {
                ResultReceiver responseObserver = intent.hasExtra(EXTRA_RESPONSE_RECEIVER) ? (ResultReceiver) intent.getParcelableExtra(EXTRA_RESPONSE_RECEIVER) : null;
                handleLogin(intent.getStringExtra(EXTRA_MAIL), Utils.Security.decryptString(intent.getStringExtra(EXTRA_CRYPTED_PASSWORD)), responseObserver);
            }
        }
    }

    private void handleLogin(String mail, String password, @Nullable ResultReceiver responseObserver) {
        HttpURLConnection connection = null;
        String returnedJson;

        try {
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/accountadministration/login");
            //Ask login
            JSONObject json = new JSONObject();
            JSONObject data = new JSONObject();

            data.put("login",mail);
            data.put("password", password);
            json.put("data", data);
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestMethod("POST");
            connection.connect();
            Utils.JSON.sendJsonOverConnection(connection, json);
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson == null || returnedJson.isEmpty()){
                if (responseObserver != null)
                    responseObserver.send(Activity.RESULT_CANCELED, null);
                return;
            }


            json = new JSONObject(returnedJson);
            if (Utils.Errors.checkAPIError(json)){
                if (responseObserver != null)
                    responseObserver.send(Activity.RESULT_CANCELED, null);
                return;
            }
            data = json.getJSONObject("data");
            Bundle answer = new Bundle();
            answer.putString(AccountManager.KEY_ACCOUNT_NAME, mail);
            answer.putString(AccountManager.KEY_ACCOUNT_TYPE, getString(R.string.sync_account_type));
            answer.putString(EXTRA_CRYPTED_PASSWORD, Utils.Security.cryptString(password));
            answer.putString(EXTRA_API_TOKEN, data.getString("token"));
            if (responseObserver != null)
                responseObserver.send(Activity.RESULT_OK, answer);
        } catch (IOException | JSONException e) {
            e.printStackTrace();
        } finally {
            if (connection != null)
                connection.disconnect();
        }
    }

    private void handleBugsSync(String apiToken, long localUID, long localPID, int offset, int limit) {
        if (apiToken.isEmpty() || localUID == -1 || localPID == -1)
            return;
        HttpURLConnection connection = null;
        String returnedJson;
        Cursor projectRole = getContentResolver().query(GrappboxContract.RolesAssignationEntry.buildRoleAssignationWithUIDAndPID(localUID, localPID), null, null, null, null);
        Cursor grappboxProjectId = getContentResolver().query(GrappboxContract.ProjectEntry.buildProjectWithLocalIdUri(localPID), new String[]{GrappboxContract.ProjectEntry.COLUMN_GRAPPBOX_ID}, null, null, null);

        if (projectRole == null || !projectRole.moveToFirst() || grappboxProjectId == null || !grappboxProjectId.moveToFirst())
            return;
        try {
            if (projectRole.getInt(projectRole.getColumnIndex(GrappboxContract.RolesEntry.COLUMN_ACCESS_BUGTRACKER)) < 1)
                return;
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/bugtracker/getlasttickets/"+apiToken+"/"+grappboxProjectId.getString(0)+"/"+String.valueOf(offset)+"/" + String.valueOf(limit));

            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestMethod("GET");
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson == null || returnedJson.isEmpty())
                return;

            JSONObject json = new JSONObject(returnedJson);
            if (Utils.Errors.checkAPIError(json))
                return;
            JSONArray bugsData = json.getJSONObject("data").getJSONArray("array");
            if (bugsData.length() == 0)
                return;
            for (int i = 0; i < bugsData.length(); ++i) {
                ContentValues currentValue = new ContentValues();
                JSONObject bug = bugsData.getJSONObject(i);
                JSONArray bugTag = bug.getJSONArray("tags");
                JSONArray bugUser = bug.getJSONArray("users");

                ContentValues[] tagAssignationValue = new ContentValues[bugTag.length()];
                ContentValues[] userAssignationValue = new ContentValues[bugUser.length()];

                String grappboxCID = bug.getJSONObject("creator").getString("id");
                Cursor creatorId = getContentResolver().query(UserEntry.buildUserWithGrappboxIdUri(grappboxCID), new String[] {UserEntry._ID}, null, null, null);

                if (creatorId == null || !creatorId.moveToFirst())
                {
                    handleUserDetailSync(apiToken, grappboxCID);
                    creatorId = getContentResolver().query(UserEntry.buildUserWithGrappboxIdUri(grappboxCID), new String[] {UserEntry._ID}, null, null, null);
                    if (creatorId == null || !creatorId.moveToFirst())
                        continue;
                }

                currentValue.put(BugEntry.COLUMN_GRAPPBOX_ID, bug.getString("id"));
                currentValue.put(BugEntry.COLUMN_LOCAL_CREATOR_ID, creatorId.getLong(0));
                creatorId.close();
                currentValue.put(BugEntry.COLUMN_LOCAL_PROJECT_ID, localPID);
                currentValue.put(BugEntry.COLUMN_TITLE, bug.getString("title"));
                currentValue.put(BugEntry.COLUMN_DESCRIPTION, bug.getString("description"));
                currentValue.putNull(BugEntry.COLUMN_LOCAL_PARENT_ID);
                Date last_edited = Utils.Date.getDateFromGrappboxAPIToUTC(bug.getJSONObject(bug.isNull("editedAt") ? "createdAt" : "editedAt").getString("date"));
                currentValue.put(BugEntry.COLUMN_DATE_LAST_EDITED_UTC, last_edited.getTime());
                Uri bugUri = getContentResolver().insert(BugEntry.CONTENT_URI, currentValue);
                if (bugUri == null)
                    continue;
                long bugId = Long.parseLong(bugUri.getLastPathSegment());

                //Insert tags
                for (int j = 0; j < bugTag.length(); ++j) {
                    JSONObject currentTag = bugTag.getJSONObject(j);
                    ContentValues tagValue = new ContentValues();
                    ContentValues tagAssignValue = new ContentValues();

                    tagValue.put(TagEntry.COLUMN_GRAPPBOX_ID, currentTag.getString("id"));
                    tagValue.put(TagEntry.COLUMN_LOCAL_PROJECT_ID, localPID);
                    tagValue.put(TagEntry.COLUMN_NAME, currentTag.getString("name"));
                    Uri tagURI = getContentResolver().insert(TagEntry.CONTENT_URI, tagValue);
                    if (tagURI == null)
                        continue;
                    tagAssignValue.put(BugTagEntry.COLUMN_LOCAL_BUG_ID, bugId);
                    tagAssignValue.put(BugTagEntry.COLUMN_LOCAL_TAG_ID, Long.parseLong(tagURI.getLastPathSegment()));
                    tagAssignationValue[j] = tagAssignValue;
                }
                getContentResolver().bulkInsert(BugTagEntry.CONTENT_URI, tagAssignationValue);

                //insert users
                for (int j = 0; j < bugTag.length(); ++j) {
                    JSONObject currentUser = bugUser.getJSONObject(j);
                    Cursor userCursor = getContentResolver().query(UserEntry.buildUserWithGrappboxIdUri(currentUser.getString("id")), new String[]{UserEntry._ID}, null, null, null);
                    ContentValues currentUserAssignation = new ContentValues();

                    if (userCursor == null || !userCursor.moveToFirst())
                    {
                        handleUserDetailSync(apiToken, currentUser.getString("id"));
                        userCursor = getContentResolver().query(UserEntry.buildUserWithGrappboxIdUri(currentUser.getString("id")), new String[]{UserEntry._ID}, null, null, null);
                        if (userCursor == null || !userCursor.moveToFirst())
                            continue;
                    }
                    currentUserAssignation.put(BugAssignationEntry.COLUMN_LOCAL_BUG_ID, bugId);
                    currentUserAssignation.put(BugAssignationEntry.COLUMN_LOCAL_USER_ID, userCursor.getLong(0));
                    userAssignationValue[j] = currentUserAssignation;
                    userCursor.close();
                }
                getContentResolver().bulkInsert(BugAssignationEntry.CONTENT_URI, userAssignationValue);
            }
        } catch (IOException e) {
            Log.e(LOG_TAG, "IOException : ", e);
        } catch (JSONException | ParseException e) {
            e.printStackTrace();
        } finally {
            projectRole.close();
            grappboxProjectId.close();
            if (connection != null)
                connection.disconnect();
        }
    }

    private void handleTimelineMessagesSync(String apiToken, long localTimelineId, int offset, int limit) {
        Cursor timelineGrappbox = getContentResolver().query(GrappboxContract.TimelineEntry.buildTimelineWithLocalIdUri(localTimelineId), new String[]{UserEntry.COLUMN_GRAPPBOX_ID}, null, null, null);
        if (timelineGrappbox == null || !timelineGrappbox.moveToFirst())
            return;
        HttpURLConnection connection = null;
        String returnedJson;
        String apiTimelineID = timelineGrappbox.getString(0);
        try{
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/timeline/getlastmessages/"+apiToken+"/"+apiTimelineID + "/" + String.valueOf(offset) + "/" + String.valueOf(limit));

            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestMethod("GET");
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson == null || returnedJson.isEmpty())
                return;

            JSONObject json = new JSONObject(returnedJson);
            if (Utils.Errors.checkAPIError(json))
                return;
            JSONArray msgs = json.getJSONObject("data").getJSONArray("array");
            if (msgs.length() == 0)
                return;
            ContentValues[] messagesValues = new ContentValues[msgs.length()];
            for (int i = 0; i < msgs.length(); ++i) {
                JSONObject current = msgs.getJSONObject(i);
                ContentValues message = new ContentValues();

                String creatorId = current.getJSONObject("creator").getString("id");
                Cursor creator = getContentResolver().query(UserEntry.buildUserWithGrappboxIdUri(creatorId), new String[]{UserEntry._ID}, null, null, null);
                if (creator == null || !creator.moveToFirst()){
                    handleUserDetailSync(apiToken, creatorId);
                    creator = getContentResolver().query(UserEntry.buildUserWithGrappboxIdUri(creatorId), new String[]{UserEntry._ID}, null, null, null);
                    if (creator == null || !creator.moveToFirst())
                        return;
                }
                message.put(TimelineMessageEntry.COLUMN_LOCAL_CREATOR_ID, creator.getLong(0));
                message.put(TimelineMessageEntry.COLUMN_LOCAL_TIMELINE_ID, localTimelineId);
                message.put(TimelineMessageEntry.COLUMN_TITLE, current.getString("title"));
                message.put(TimelineMessageEntry.COLUMN_MESSAGE, current.getString("message"));
                if (current.has("parentId"))
                    message.put(TimelineMessageEntry.COLUMN_PARENT_ID, current.getString("parentId"));
                else
                    message.putNull(TimelineMessageEntry.COLUMN_PARENT_ID);
                Date lastEditedMsg = Utils.Date.getDateFromGrappboxAPIToUTC(current.isNull("editedAt") ? current.getJSONObject("createdAt").getString("date") : current.getJSONObject("editedAt").getString("date"));

                message.put(TimelineMessageEntry.COLUMN_DATE_LAST_EDITED_AT_UTC, lastEditedMsg.getTime());
                message.put(TimelineMessageEntry.COLUMN_COUNT_ANSWER, Integer.valueOf(current.getString("nbComment")));
                messagesValues[i] = message;
                creator.close();
            }
            getContentResolver().bulkInsert(TimelineMessageEntry.CONTENT_URI, messagesValues);
        } catch (IOException | JSONException | ParseException e) {
            e.printStackTrace();
        } finally {
            timelineGrappbox.close();
            if (connection != null)
                connection.disconnect();
        }

    }

    private void handleUserDetailSync(String apiToken, long localUID) {
        Cursor userGrappboxID = getContentResolver().query(UserEntry.buildUserWithLocalIdUri(localUID), new String[]{UserEntry.COLUMN_GRAPPBOX_ID}, null, null, null);

        if (userGrappboxID == null || !userGrappboxID.moveToFirst())
            return;
        try {
            handleUserDetailSync(apiToken, userGrappboxID.getString(0));
        } finally {
            userGrappboxID.close();
        }
    }

    private void handleUserDetailSync(String apiToken, String apiUID) {
        if (apiToken.isEmpty() || apiUID.isEmpty())
            return;
        HttpURLConnection connection = null;
        String returnedJson;

        try {
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/getuserbasicinformations/"+apiToken+"/"+apiUID);

            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestMethod("GET");
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson == null || returnedJson.isEmpty())
                return;

            JSONObject json = new JSONObject(returnedJson);
            if (Utils.Errors.checkAPIError(json))
                return;
            JSONObject data = json.getJSONObject("data");
            ContentValues userValue = new ContentValues();
            userValue.put(UserEntry.COLUMN_GRAPPBOX_ID, apiUID);
            userValue.put(UserEntry.COLUMN_FIRSTNAME, data.getString("firstname"));
            userValue.put(UserEntry.COLUMN_LASTNAME, data.getString("lastname"));
            userValue.put(UserEntry.COLUMN_DATE_BIRTHDAY_UTC, data.getString("birthday"));
            userValue.put(UserEntry.COLUMN_CONTACT_EMAIL, data.getString("email"));
            userValue.put(UserEntry.COLUMN_CONTACT_PHONE, data.getString("phone"));
            userValue.put(UserEntry.COLUMN_COUNTRY, data.getString("country"));
            userValue.put(UserEntry.COLUMN_SOCIAL_LINKEDIN, data.getString("linkedin"));
            userValue.put(UserEntry.COLUMN_SOCIAL_TWITTER, data.getString("twitter"));
            getContentResolver().insert(UserEntry.CONTENT_URI, userValue);
        } catch (IOException e) {
            Log.e(LOG_TAG, "IOException : ", e);
        } catch (JSONException e) {
            e.printStackTrace();
        } finally {
            if (connection != null)
                connection.disconnect();
        }
    }

    private void handleEventTypeSync(String apiToken) throws IOException, JSONException {
        if (apiToken.isEmpty())
            return;
        HttpURLConnection connection = null;
        String returnedJson;

        try {
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/event/gettypes/" + apiToken);

            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestMethod("GET");
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson == null || returnedJson.isEmpty())
                return;

            JSONObject json = new JSONObject(returnedJson);
            if (Utils.Errors.checkAPIError(json))
                return;
            JSONArray typesData = json.getJSONObject("data").getJSONArray("array");
            if (typesData.length() == 0)
                return;
            ContentValues[] typesValues = new ContentValues[typesData.length()];
            for (int i = 0; i < typesData.length(); ++i) {
                JSONObject current = typesData.getJSONObject(i);
                ContentValues value = new ContentValues();

                value.put(EventTypeEntry.COLUMN_GRAPPBOX_ID, current.getString("id"));
                value.put(EventTypeEntry.COLUMN_NAME, current.getString("name"));
                typesValues[i] = value;
            }
            getContentResolver().bulkInsert(EventTypeEntry.CONTENT_URI, typesValues);
        } finally {
            if (connection != null)
                connection.disconnect();
        }
    }

    private void handleNextMeetingsSync(String apiToken, long localPID) {
        if (apiToken.isEmpty() || localPID == -1)
            return;
        HttpURLConnection connection = null;
        String returnedJson;
        Cursor grappboxProjectId = getContentResolver().query(GrappboxContract.ProjectEntry.buildProjectWithLocalIdUri(localPID), new String[]{GrappboxContract.ProjectEntry.COLUMN_GRAPPBOX_ID}, null, null, null);
        if (grappboxProjectId == null || !grappboxProjectId.moveToFirst())
            return;
        try {
            handleEventTypeSync(apiToken);
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/dashboard/getnextmeetings/" + apiToken + "/" + grappboxProjectId.getString(0));

            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestMethod("GET");
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson == null || returnedJson.isEmpty())
                return;

            JSONObject json = new JSONObject(returnedJson);
            if (Utils.Errors.checkAPIError(json))
                return;
            JSONArray nextMeetingsData = json.getJSONObject("data").getJSONArray("array");
            if (nextMeetingsData.length() == 0)
                return;
            ContentValues[] nextMeetingsValues = new ContentValues[nextMeetingsData.length()];
            for (int i = 0; i < nextMeetingsData.length(); ++i) {
                ContentValues nextMeeting = new ContentValues();
                JSONObject current = nextMeetingsData.getJSONObject(i);

                nextMeeting.put(EventEntry.COLUMN_GRAPPBOX_ID, current.getString("id"));
                Cursor typeCursor = getContentResolver().query(EventTypeEntry.CONTENT_URI, new String[]{EventTypeEntry._ID}, EventTypeEntry.COLUMN_NAME + "=?", new String[] {current.getString("type")}, null);
                if (typeCursor == null || !typeCursor.moveToFirst())
                    continue;
                nextMeeting.put(EventEntry.COLUMN_LOCAL_EVENT_TYPE_ID, typeCursor.getLong(0));
                nextMeeting.put(EventEntry.COLUMN_EVENT_DESCRIPTION, current.getString("description"));
                nextMeeting.put(EventEntry.COLUMN_EVENT_TITLE, current.getString("title"));
                nextMeeting.put(EventEntry.COLUMN_DATE_BEGIN_UTC, Utils.Date.getDateFromGrappboxAPIToUTC(current.getJSONObject("begin_date").getString("date")).getTime());
                nextMeeting.put(EventEntry.COLUMN_DATE_END_UTC, Utils.Date.getDateFromGrappboxAPIToUTC(current.getJSONObject("end_date").getString("date")).getTime());
                typeCursor.close();
                nextMeetingsValues[i] = nextMeeting;
            }
            getContentResolver().bulkInsert(EventEntry.CONTENT_URI, nextMeetingsValues);
        } catch (IOException e) {
            Log.e(LOG_TAG, "IOException : ", e);
        } catch (JSONException | ParseException e) {
            e.printStackTrace();
        } finally {
            grappboxProjectId.close();
            if (connection != null)
                connection.disconnect();
        }
    }
}
