/*
 * Created by Marc Wieser the 16/11/2016
 * If you have any problem or question about this work
 * please contact the author at marc.wieser@gmail.com
 *
 * The following code is owned by GrappBox you can't
 * use it without any authorization or special instructions
 * GrappBox © 2016
 */

package com.grappbox.grappbox.messaging;

import android.accounts.NetworkErrorException;
import android.app.Service;
import android.content.ContentValues;
import android.content.Intent;
import android.content.SharedPreferences;
import android.database.Cursor;
import android.net.Uri;
import android.os.IBinder;
import android.util.Log;

import com.google.firebase.iid.FirebaseInstanceId;
import com.google.firebase.iid.FirebaseInstanceIdService;
import com.grappbox.grappbox.BuildConfig;
import com.grappbox.grappbox.Utils;
import com.grappbox.grappbox.data.GrappboxContract;
import com.grappbox.grappbox.sync.GrappboxJustInTimeService;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;
import java.net.HttpURLConnection;
import java.net.URI;
import java.net.URL;

import static com.google.android.gms.internal.zzs.TAG;

/**
 * Created by Marc Wieser the 21/10/2016.
 * If you have any problem or question about this work
 * please contact the author at marc.wieser33@gmail.com
 *
 * The following code is owned by GrappBox you can't
 * use it without any authorization or special instructions
 * GrappBox © 2016
 */

public class FirebaseCloudMessagingService extends FirebaseInstanceIdService {
    public static final String SHARED_FIREBASE_PREF = "grappbox-firebase-prefs";
    public static final String FIREBASE_PREF_TOKEN = "firebase-token";

    public FirebaseCloudMessagingService() {
    }

    @Override
    public void onTokenRefresh() {
        // Get updated InstanceID token.
        String refreshedToken = FirebaseInstanceId.getInstance().getToken();
        SharedPreferences prefs = getSharedPreferences(SHARED_FIREBASE_PREF, MODE_PRIVATE);
        prefs.edit().putString(FIREBASE_PREF_TOKEN, refreshedToken).apply();
        Intent register = new Intent(this, GrappboxJustInTimeService.class);
        register.setAction(GrappboxJustInTimeService.ACTION_REGISTER_DEVICE);
        startService(register);
    }
}
