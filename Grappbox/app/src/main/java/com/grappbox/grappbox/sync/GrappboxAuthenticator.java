package com.grappbox.grappbox.sync;

import android.accounts.AbstractAccountAuthenticator;
import android.accounts.Account;
import android.accounts.AccountAuthenticatorResponse;
import android.accounts.AccountManager;
import android.accounts.NetworkErrorException;
import android.annotation.SuppressLint;
import android.content.Context;
import android.content.Intent;
import android.os.Build;
import android.os.Bundle;
import android.provider.Settings;
import android.support.annotation.NonNull;
import android.util.Log;

import com.grappbox.grappbox.AddAccountActivity;
import com.grappbox.grappbox.BuildConfig;
import com.grappbox.grappbox.Utils;
import com.grappbox.grappbox.singleton.Session;

import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;
import java.net.HttpURLConnection;
import java.net.URL;
import java.util.Calendar;

import static android.accounts.AccountManager.KEY_INTENT;

/**
 * Created by Marc Wieser the 03/09/2016.
 * If you have any problem or question about this work
 * please contact the author at marc.wieser33@gmail.com
 *
 * The following code is owned by GrappBox you can't
 * use it without any authorization or special instructions
 * GrappBox © 2016
 */
public class GrappboxAuthenticator extends AbstractAccountAuthenticator {
    public static final String ACCOUNT_TOKEN_TYPE = "GRAPPBOX::APP::API";
    private static final String LOG_TAG = GrappboxAuthenticator.class.getSimpleName();
    private Context mContext;

    public GrappboxAuthenticator(Context context) {
        super(context);
        mContext = context;
    }

    @Override
    public Bundle editProperties(AccountAuthenticatorResponse accountAuthenticatorResponse, String s) {
        return null;
    }

    private Bundle constructLoginIntent(AccountAuthenticatorResponse response) {
        Intent launchAddAccountScreen = new Intent(mContext, AddAccountActivity.class);
        launchAddAccountScreen.putExtra(AccountManager.KEY_ACCOUNT_MANAGER_RESPONSE, response);

        Bundle ret = new Bundle();
        ret.putParcelable(KEY_INTENT, launchAddAccountScreen);
        return ret;
    }

    @Override
    public Bundle addAccount(@NonNull AccountAuthenticatorResponse response, @NonNull String accountType, String authTokenType, String[] requiredFeatures, Bundle options) throws NetworkErrorException {
        return constructLoginIntent(response);
    }

    @SuppressLint("HardwareIds")
    @Override
    public Bundle getAuthToken(AccountAuthenticatorResponse response, Account account, String s, Bundle bundle) throws NetworkErrorException {
        HttpURLConnection connection = null;
        String returnedJson;
        AccountManager am = AccountManager.get(mContext);
        Bundle answer = new Bundle();

        try {
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/account/login");
            //Ask login
            JSONObject json = new JSONObject();
            JSONObject data = new JSONObject();

            data.put("login",account.name);
            data.put("password", Utils.Security.decryptString(am.getPassword(account)));
            data.put("mac", Settings.Secure.getString(mContext.getContentResolver(), Settings.Secure.ANDROID_ID));
            data.put("flag", "and");
            data.put("device_name", Build.MODEL + " " + Build.SERIAL);
            json.put("data", data);
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestMethod("POST");
            connection.connect();
            Utils.JSON.sendJsonOverConnection(connection, json);
            Log.d(LOG_TAG, json.toString());
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson == null || returnedJson.isEmpty()){
                if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.LOLLIPOP_MR1) {
                    am.removeAccountExplicitly(account);
                } else {
                    am.removeAccount(account, null, null);
                }
                return constructLoginIntent(response);
            }

            json = new JSONObject(returnedJson);
            if (Utils.Errors.checkAPIError(json)){
                if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.LOLLIPOP_MR1) {
                    am.removeAccountExplicitly(account);
                } else {
                    am.removeAccount(account, null, null);
                }
                return constructLoginIntent(response);
            }
            data = json.getJSONObject("data");
            Calendar cal = Calendar.getInstance();
            cal.add(Calendar.DATE, 1);
            cal.add(Calendar.HOUR, -2);
            answer.putString(AccountManager.KEY_ACCOUNT_NAME, account.name);
            answer.putString(AccountManager.KEY_ACCOUNT_TYPE, account.type);
            answer.putString(AccountManager.KEY_AUTHTOKEN, data.getString("token"));
            am.setUserData(account, GrappboxJustInTimeService.EXTRA_API_TOKEN, data.getString("token"));
            am.setUserData(account, Session.ACCOUNT_EXPIRATION_TOKEN, String.valueOf(cal.getTimeInMillis()));
        } catch (IOException | JSONException e) {
            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.LOLLIPOP_MR1) {
                am.removeAccountExplicitly(account);
            } else {
                am.removeAccount(account, null, null);
            }
            return constructLoginIntent(response);
        } finally {
            if (connection != null)
                connection.disconnect();
        }
        return answer;
    }

    @Override
    public String getAuthTokenLabel(String s) {
        throw new UnsupportedOperationException();
    }

    @Override
    public Bundle updateCredentials(AccountAuthenticatorResponse accountAuthenticatorResponse, Account account, String s, Bundle bundle) throws NetworkErrorException {
        throw new UnsupportedOperationException();
    }

    @Override
    public Bundle hasFeatures(AccountAuthenticatorResponse accountAuthenticatorResponse, Account account, String[] strings) throws NetworkErrorException {
        throw new UnsupportedOperationException();
    }

    @Override
    public Bundle confirmCredentials(AccountAuthenticatorResponse accountAuthenticatorResponse, Account account, Bundle bundle) throws NetworkErrorException {
        throw new UnsupportedOperationException();
    }

}
