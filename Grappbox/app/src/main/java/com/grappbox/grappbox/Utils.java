package com.grappbox.grappbox;

import android.accounts.AccountManager;
import android.accounts.AuthenticatorException;
import android.accounts.OperationCanceledException;
import android.app.Activity;
import android.content.ContentValues;
import android.content.Context;
import android.content.res.TypedArray;
import android.net.ConnectivityManager;
import android.net.NetworkInfo;
import android.support.v4.content.ContextCompat;
import android.support.v4.net.ConnectivityManagerCompat;
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
import java.lang.annotation.Documented;
import java.net.HttpURLConnection;
import java.net.URLEncoder;
import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.TimeZone;

/**
 * Created by marcw on 31/08/2016.
 */
public class Utils {

    public static class Date {
        public final static SimpleDateFormat grappboxFormatter = new SimpleDateFormat("yyyy-MM-dd hh:mm:ss");
        public final static TimeZone grappboxTZ = TimeZone.getTimeZone("UTC");
        public final static TimeZone phoneTZ = TimeZone.getDefault();
        public final static TimeZone utcTZ = TimeZone.getTimeZone("UTC");

        public static java.util.Date getDateFromGrappboxAPIToUTC(String formattedDate) throws ParseException {
            java.util.Date grappboxDate;

            grappboxFormatter.setTimeZone(grappboxTZ);
            grappboxDate = grappboxFormatter.parse(formattedDate);
            grappboxFormatter.setTimeZone(utcTZ);
            String temp = grappboxFormatter.format(grappboxDate);
            return grappboxFormatter.parse(temp);
        }

        public static java.util.Date getDateFromGrappboxAPIToPhone(String formattedDate) throws ParseException {
            java.util.Date grappboxDate;

            grappboxFormatter.setTimeZone(grappboxTZ);
            grappboxDate = grappboxFormatter.parse(formattedDate);
            grappboxFormatter.setTimeZone(phoneTZ);
            String temp = grappboxFormatter.format(grappboxDate);
            return grappboxFormatter.parse(temp);
        }

        public static java.util.Date convertUTCToGrappbox (java.util.Date date) throws ParseException {
            grappboxFormatter.setTimeZone(grappboxTZ);
            return grappboxFormatter.parse(grappboxFormatter.format(date));
        }

        public static java.util.Date convertUTCToPhone(java.util.Date date) throws ParseException {
            grappboxFormatter.setTimeZone(phoneTZ);
            return grappboxFormatter.parse(grappboxFormatter.format(date));
        }
    }

    public static class Errors {

        public static boolean checkAPIError(JSONObject json) throws JSONException {
            return !(json.getJSONObject("info").getString("return_code").startsWith("1."));
        }

        public static boolean checkAPIErrorToDisplay(Context context, JSONObject json) throws JSONException {
            String error = "";
            if (!checkAPIError(json))
                return false;
            //TODO : Display error
            return true;
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
}
