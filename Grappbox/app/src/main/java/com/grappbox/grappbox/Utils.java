package com.grappbox.grappbox;

import android.accounts.AccountManager;
import android.accounts.AuthenticatorException;
import android.accounts.OperationCanceledException;
import android.app.Activity;
import android.content.ContentUris;
import android.content.Context;
import android.content.res.TypedArray;
import android.database.Cursor;
import android.net.ConnectivityManager;
import android.net.NetworkInfo;
import android.net.Uri;
import android.os.Environment;
import android.provider.DocumentsContract;
import android.provider.MediaStore;
import android.util.Base64;
import android.util.Log;
import android.util.TypedValue;

import com.grappbox.grappbox.singleton.Session;
import com.grappbox.grappbox.sync.GrappboxAuthenticator;

import org.json.JSONException;
import org.json.JSONObject;

import java.io.BufferedReader;
import java.io.DataOutputStream;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.net.HttpURLConnection;
import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.Locale;
import java.util.TimeZone;

import static com.grappbox.grappbox.ProjectActivity.FRAGMENT_TAG_BUGTRACKER;
import static com.grappbox.grappbox.ProjectActivity.FRAGMENT_TAG_CALENDAR;
import static com.grappbox.grappbox.ProjectActivity.FRAGMENT_TAG_CLOUD;
import static com.grappbox.grappbox.ProjectActivity.FRAGMENT_TAG_DASHBOARD;
import static com.grappbox.grappbox.ProjectActivity.FRAGMENT_TAG_GANTT;
import static com.grappbox.grappbox.ProjectActivity.FRAGMENT_TAG_TASK;
import static com.grappbox.grappbox.ProjectActivity.FRAGMENT_TAG_TIMELINE;
import static com.grappbox.grappbox.ProjectActivity.FRAGMENT_TAG_WHITEBOARD;

/**
 * Created by marcw on 31/08/2016.
 */
public class Utils {

    public static class Date {
        private final static String LOG_TAG = Date.class.getSimpleName();
        public final static SimpleDateFormat grappboxFormatter = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss.SSSSSS", Locale.getDefault());
        public final static TimeZone grappboxTZ = TimeZone.getTimeZone("UTC");
        public final static TimeZone phoneTZ = TimeZone.getDefault();
        public final static TimeZone utcTZ = TimeZone.getTimeZone("UTC");

        public static String getDateFromGrappboxAPIToUTC(String formattedDate) throws ParseException {
            java.util.Date grappboxDate;
            grappboxFormatter.setTimeZone(grappboxTZ);
            grappboxDate = grappboxFormatter.parse(formattedDate);
            grappboxFormatter.setTimeZone(utcTZ);
            return grappboxFormatter.format(grappboxDate);
        }

        public static java.util.Date getDateFromUTCAPIToPhone(String formattedDate) throws ParseException {
            java.util.Date grappboxDate;

            grappboxFormatter.setTimeZone(utcTZ);
            grappboxDate = grappboxFormatter.parse(formattedDate);
            grappboxFormatter.setTimeZone(phoneTZ);
            String temp = grappboxFormatter.format(grappboxDate);
            return grappboxFormatter.parse(temp);
        }

        public static java.util.Date convertUTCToGrappbox (java.util.Date date) throws ParseException {
            grappboxFormatter.setTimeZone(grappboxTZ);
            return grappboxFormatter.parse(grappboxFormatter.format(date));
        }

        public static java.util.Date convertUTCToPhone(String date) throws ParseException {
            grappboxFormatter.setTimeZone(utcTZ);
            java.util.Date temp = grappboxFormatter.parse(date);
            grappboxFormatter.setTimeZone(phoneTZ);
            String tempDate = grappboxFormatter.format(temp);
            return grappboxFormatter.parse(tempDate);
        }

        public static String nowUTC() throws ParseException {
            java.util.Date now = new java.util.Date();
            grappboxFormatter.setTimeZone(phoneTZ);
            String phoneStr = grappboxFormatter.format(now);
            grappboxFormatter.setTimeZone(utcTZ);
            java.util.Date nowUTC = grappboxFormatter.parse(phoneStr);
            return grappboxFormatter.format(nowUTC);
        }
    }

    public static class Errors {

        public static boolean checkAPIError(JSONObject json) throws JSONException {
            return !(json.getJSONObject("info").getString("return_code").startsWith("1."));
        }

        public static String getClientMessageFromErrorCode(Context context, String errorCode){
            int errorType = Integer.parseInt(errorCode.split("\\.")[2]);

            switch (errorType){
                case 7:
                    return context.getString(R.string.error_already_in_database);
                case 8:
                    return context.getString(R.string.error_network);
                case 9:
                    return context.getString(R.string.error_access_rights);
                case 10:
                    return context.getString(R.string.error_resource_not_found);
                default:
                    return context.getString(R.string.error_unkown);
            }
        }
    }

    public static class JSON {
        public static void sendJsonOverConnection(HttpURLConnection connection, JSONObject json) throws IOException {
            DataOutputStream writer = new DataOutputStream(connection.getOutputStream());

            writer.write(json.toString().getBytes("UTF-8"));
            writer.flush();
            writer.close();
        }

        public static String readDataFromConnection(HttpURLConnection connection) throws IOException {
            InputStream inputStream = connection.getInputStream();
            StringBuffer buffer = new StringBuffer();
            if (inputStream == null) {
                return null;
            }
            BufferedReader reader = new BufferedReader(new InputStreamReader(inputStream));

            String line;
            while ((line = reader.readLine()) != null) {
                buffer.append(line);
            }

            if (buffer.length() == 0) {
                return null;
            }
            return buffer.toString();
        }
    }

    public static class Network {
        public static boolean haveInternetConnection(Context context) {
            ConnectivityManager cm = (ConnectivityManager) context.getSystemService(Context.CONNECTIVITY_SERVICE);
            NetworkInfo activeNetwork = cm.getActiveNetworkInfo();
            return activeNetwork != null && activeNetwork.isConnectedOrConnecting();
        }

        public static int getActiveConnectionType(Context context) {
            ConnectivityManager cm = (ConnectivityManager) context.getSystemService(Context.CONNECTIVITY_SERVICE);
            NetworkInfo activeNetwork = cm.getActiveNetworkInfo();
            return activeNetwork.getType();
        }

    }

    public static class Security {
        public static String cryptString(String clearString) {
            return Base64.encodeToString(clearString.getBytes(), Base64.DEFAULT);
        }

        public static String decryptString(String cryptedString) {
            return new String(Base64.decode(cryptedString, Base64.DEFAULT));
        }
    }

    public static class Color {
        public static int getThemeAccentColor(Context context){
            TypedValue typedValue = new TypedValue();

            TypedArray a = context.obtainStyledAttributes(typedValue.data, new int[] { R.attr.colorAccent });
            int color = a.getColor(0, 0);

            a.recycle();

            return color;
        }
    }

    public static class Account{
        public static String getAuthToken(Activity activity){
            String token = null;
            AccountManager am = AccountManager.get(activity);
            try {
                token = am.getAuthToken(Session.getInstance(activity).getCurrentAccount(), GrappboxAuthenticator.ACCOUNT_TOKEN_TYPE, null, activity, null, null).getResult().getString(AccountManager.KEY_AUTHTOKEN);
            } catch (OperationCanceledException | IOException | AuthenticatorException e) {
                e.printStackTrace();
            }
            return token;
        }

        public static String getAuthTokenService(Context context, android.accounts.Account account){
            String token = null;
            AccountManager am = AccountManager.get(context);
            if (account == null)
                account = Session.getInstance(context).getCurrentAccount();
            try {
                token = am.getAuthToken(account, GrappboxAuthenticator.ACCOUNT_TOKEN_TYPE, null, true, null, null).getResult().getString(AccountManager.KEY_AUTHTOKEN);
            } catch (OperationCanceledException | IOException | AuthenticatorException e) {
                e.printStackTrace();
            }
            return token;
        }
    }

    public static class File {
        private static final String LOG_TAG = File.class.getSimpleName();
        public static String getPath(Context context, Uri uri) {

            // DocumentProvider
            if (DocumentsContract.isDocumentUri(context, uri)) {
                // ExternalStorageProvider
                if (isExternalStorageDocument(uri)) {
                    final String docId = DocumentsContract.getDocumentId(uri);
                    final String[] split = docId.split(":");
                    final String type = split[0];

                    if ("primary".equalsIgnoreCase(type)) {
                        return Environment.getExternalStorageDirectory() + "/" + split[1];
                    }
                    throw new UnsupportedOperationException(context.getString(R.string.error_file_not_supported));
                }
                // DownloadsProvider
                else if (isDownloadsDocument(uri)) {
                    final String id = DocumentsContract.getDocumentId(uri);
                    final Uri contentUri = ContentUris.withAppendedId(Uri.parse("content://downloads/public_downloads"), Long.valueOf(id));
                    return getDataColumn(context, contentUri, null, null);
                }
                // MediaProvider
                else
                if (isMediaDocument(uri)) {
                    final String docId = DocumentsContract.getDocumentId(uri);
                    final String[] split = docId.split(":");
                    final String type = split[0];
                    Uri contentUri = null;
                    if ("image".equals(type)) {
                        contentUri = MediaStore.Images.Media.EXTERNAL_CONTENT_URI;
                    } else if ("video".equals(type)) {
                        contentUri = MediaStore.Video.Media.EXTERNAL_CONTENT_URI;
                    } else if ("audio".equals(type)) {
                        contentUri = MediaStore.Audio.Media.EXTERNAL_CONTENT_URI;
                    }
                    final String selection = "_id=?";
                    final String[] selectionArgs = new String[] {split[1]};
                    return getDataColumn(context, contentUri, selection, selectionArgs);
                }
            }
            // MediaStore (and general)
            else if ("content".equalsIgnoreCase(uri.getScheme())) {
                // Return the remote address
                Log.d(LOG_TAG, "General file");
                if (isGooglePhotosUri(uri))
                    return uri.getLastPathSegment();
                return getDataColumn(context, uri, null, null);
            }
            // File
            else if ("file".equalsIgnoreCase(uri.getScheme())) {
                return uri.getPath();
            }
            return null;
        }

        public static String getDataColumn(Context context, Uri uri, String selection, String[] selectionArgs) {
            Cursor cursor = null;
            final String column = "_data";
            final String[] projection = { column };
            try {
                cursor = context.getContentResolver().query(uri, projection, selection, selectionArgs, null);
                if (cursor != null && cursor.moveToFirst()) {
                    final int index = cursor.getColumnIndexOrThrow(column);
                    return cursor.getString(index);
                }
            } finally {
                if (cursor != null)
                    cursor.close();
            }
            return null;
        }

        public static boolean isExternalStorageDocument(Uri uri) {
            return "com.android.externalstorage.documents".equals(uri.getAuthority());
        }

        public static boolean isDownloadsDocument(Uri uri) {
            return "com.android.providers.downloads.documents".equals(uri.getAuthority());
        }


        public static boolean isMediaDocument(Uri uri) {
            return "com.android.providers.media.documents".equals(uri.getAuthority());
        }


        public static boolean isGooglePhotosUri(Uri uri) {
            return "com.google.android.apps.photos.content".equals(uri.getAuthority());
        }

    }

    public static class Design{
        public static int getThemeIDFromMenuID(int menuID){
            switch (menuID){
                case R.id.nav_dashboard:
                    return R.style.DashboardTheme;
                case R.id.nav_calendar:
                    return R.style.CalendarTheme;
                case R.id.nav_cloud:
                    return R.style.CloudTheme;
                case R.id.nav_timeline:
                    return R.style.TimelineTheme;
                case R.id.nav_bugtracker:
                    return R.style.BugtrackerTheme;
                case R.id.nav_tasks:
                    return R.style.TaskTheme;
                case R.id.nav_gantt:
                    return R.style.GanttTheme;
                case R.id.nav_whiteboard:
                    return R.style.WhiteboardTheme;
                default:
                    throw new IllegalArgumentException("Unexpected menu ID");
            }
        }
       public static int getThemeIDFromFragmentTAG(String tag){
           switch (tag){
               case FRAGMENT_TAG_DASHBOARD:
                   return R.style.DashboardTheme;
               case FRAGMENT_TAG_CALENDAR:
                   return R.style.CalendarTheme;
               case FRAGMENT_TAG_CLOUD:
                   return R.style.CloudTheme;
               case FRAGMENT_TAG_TIMELINE:
                   return R.style.TimelineTheme;
               case FRAGMENT_TAG_BUGTRACKER:
                   return R.style.BugtrackerTheme;
               case FRAGMENT_TAG_TASK:
                   return R.style.TaskTheme;
               case FRAGMENT_TAG_GANTT:
                   return R.style.GanttTheme;
               case FRAGMENT_TAG_WHITEBOARD:
                   return R.style.WhiteboardTheme;
               default:
                   throw new IllegalArgumentException("Unexpected tag ID");
           }
       }
    }

}
