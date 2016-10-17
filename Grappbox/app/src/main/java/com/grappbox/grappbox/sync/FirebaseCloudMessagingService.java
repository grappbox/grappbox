package com.grappbox.grappbox.sync;

import android.accounts.NetworkErrorException;
import android.app.Service;
import android.content.ContentValues;
import android.content.Intent;
import android.database.Cursor;
import android.net.Uri;
import android.os.IBinder;
import android.util.Log;

import com.google.firebase.iid.FirebaseInstanceId;
import com.google.firebase.iid.FirebaseInstanceIdService;
import com.grappbox.grappbox.BuildConfig;
import com.grappbox.grappbox.Utils;
import com.grappbox.grappbox.data.GrappboxContract;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;
import java.net.HttpURLConnection;
import java.net.URI;
import java.net.URL;

import static com.google.android.gms.internal.zzs.TAG;

public class FirebaseCloudMessagingService extends FirebaseInstanceIdService {
    public static final String APITOKEN_0_3 = "123456789";
    public FirebaseCloudMessagingService() {
    }

    @Override
    public void onTokenRefresh() {
        // Get updated InstanceID token.
        String refreshedToken = FirebaseInstanceId.getInstance().getToken();
        Log.d(TAG, "Refreshed token: " + refreshedToken);

        // TODO: Implement this method to send any registration to your app's servers.
        sendRegistrationToServer(refreshedToken);
    }

    public void sendRegistrationToServer(String firebaseToken){
        HttpURLConnection connection = null;
        String returnedJson;


        try {
            final URL url = new URL("https://api.grappbox.com/0.3/notification/device");
            JSONObject json = new JSONObject();
            JSONObject data = new JSONObject();

            data.put("device_type", "Android");
            data.put("device_token", firebaseToken);
            data.put("device_name", "My Android Device");
            json.put("data", data);
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestProperty("Authorization", APITOKEN_0_3);
            connection.setRequestMethod("POST");
            Utils.JSON.sendJsonOverConnection(connection, json);
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson == null || returnedJson.isEmpty()){
                throw new NetworkErrorException("Returned JSON is empty");
            } else {
                json = new JSONObject(returnedJson);
                if (Utils.Errors.checkAPIError(json)){
                    throw new NetworkErrorException("API error");
                }
            }
        } catch (IOException | JSONException | NetworkErrorException e) {
            e.printStackTrace();
        } finally {
            if (connection != null)
                connection.disconnect();
        }
    }
}
