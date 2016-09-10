package com.grappbox.grappbox.sync;

import android.accounts.AbstractAccountAuthenticator;
import android.accounts.Account;
import android.accounts.AccountAuthenticatorResponse;
import android.accounts.AccountManager;
import android.accounts.NetworkErrorException;
import android.content.ContentValues;
import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.support.annotation.NonNull;

import com.grappbox.grappbox.AddAccountActivity;
import com.grappbox.grappbox.BuildConfig;
import com.grappbox.grappbox.data.GrappboxContract;
import com.grappbox.grappbox.singleton.Session;
import com.grappbox.grappbox.Utils;

import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;
import java.net.HttpURLConnection;
import java.net.URL;
import java.util.Calendar;

/**
 * Created by marcw on 03/09/2016.
 */
public class GrappboxAuthenticator extends AbstractAccountAuthenticator {
    private Context mContext;

    public GrappboxAuthenticator(Context context) {
        super(context);
        mContext = context;
    }

    @Override
    public Bundle editProperties(AccountAuthenticatorResponse accountAuthenticatorResponse, String s) {
        //TODO : return intent to device specific preferences concerning syncing on WI-FI and Radio Cell
        return null;
    }

    private Bundle constructLoginIntent(AccountAuthenticatorResponse response) {
        Intent launchAddAccountScreen = new Intent(mContext, AddAccountActivity.class);
        launchAddAccountScreen.putExtra(AccountManager.KEY_ACCOUNT_MANAGER_RESPONSE, response);

        Bundle ret = new Bundle();
        ret.putParcelable(AccountManager.KEY_INTENT, launchAddAccountScreen);
        return ret;
    }

    @Override
    public Bundle addAccount(@NonNull AccountAuthenticatorResponse response, @NonNull String accountType, String authTokenType, String[] requiredFeatures, Bundle options) throws NetworkErrorException {
        return constructLoginIntent(response);
    }

    @Override
    public Bundle getAuthToken(AccountAuthenticatorResponse response, Account account, String s, Bundle bundle) throws NetworkErrorException {
        HttpURLConnection connection = null;
        String returnedJson;
        AccountManager am = AccountManager.get(mContext);
        Bundle answer = new Bundle();

        try {
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/accountadministration/login");
            //Ask login
            JSONObject json = new JSONObject();
            JSONObject data = new JSONObject();

            data.put("login",account.name);
            data.put("password", am.getPassword(account));
            json.put("data", data);
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestMethod("POST");
            connection.connect();
            Utils.JSON.sendJsonOverConnection(connection, json);
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson == null || returnedJson.isEmpty()){
                return constructLoginIntent(response);
            }

            json = new JSONObject(returnedJson);
            if (Utils.Errors.checkAPIError(json)){
                return constructLoginIntent(response);
            }
            data = json.getJSONObject("data");
            Calendar cal = Calendar.getInstance();
            cal.add(Calendar.DATE, 1);
            cal.add(Calendar.HOUR, -2);
            answer.putString(AccountManager.KEY_AUTHTOKEN, data.getString("token"));
            am.setUserData(account, GrappboxJustInTimeService.EXTRA_API_TOKEN, data.getString("token"));
            am.setUserData(account, Session.ACCOUNT_EXPIRATION_TOKEN, String.valueOf(cal.getTimeInMillis()));

        } catch (IOException | JSONException e) {
            e.printStackTrace();
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
