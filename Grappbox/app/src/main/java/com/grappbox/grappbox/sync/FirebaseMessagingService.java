package com.grappbox.grappbox.sync;

import android.app.Notification;
import android.app.NotificationManager;
import android.app.PendingIntent;
import android.app.Service;
import android.content.Intent;
import android.util.Log;

import com.google.firebase.messaging.RemoteMessage;
import com.grappbox.grappbox.DebugActivity;
import com.grappbox.grappbox.R;
import com.grappbox.grappbox.interfaces.MessagingDispatcher;

import org.json.JSONException;
import org.json.JSONObject;

import java.util.Map;

/**
 * Created by Marc Wieser on 21/10/2016.
 * If you have any problem or question about this work
 * please contact the author at marc.wieser33@gmail.com
 *
 * The following code is owned by GrappBox you can't
 * use it without any authorization or special instructions
 * GrappBox © 2016
 */

public class FirebaseMessagingService extends com.google.firebase.messaging.FirebaseMessagingService {
    public static final String TAG = FirebaseMessagingService.class.getSimpleName();
    private MessagingDispatcher mBugDispatcher, mEventDispatcher;
    static int idNotif = 0;

    public FirebaseMessagingService() {
        mBugDispatcher = new BugMessagingDispatcher(this);
        mEventDispatcher = new EventMessagingDispatcher();
    }

    @Override
    public void onMessageReceived(RemoteMessage remoteMessage) {

        Log.d(TAG, "Notif received");
        Notification.Builder builder = new Notification.Builder(this);
        Map<String, String> data = remoteMessage.getData();
        String[] splittedAction = data.get("title").split(" ");
        String actionType = splittedAction[splittedAction.length - 1];
        try {
            String action = data.get("title");
            JSONObject body = new JSONObject(data.get("body"));
            if ("bug".equals(actionType)){
                mBugDispatcher.dispatch(action, body);
            } else if ("event".equals(actionType)){
                mEventDispatcher.dispatch(action, body);
            }
        } catch (JSONException e) {
            e.printStackTrace();
        }

        //The following code is a debug code to show API sended messages
        //TODO : delete it
        builder.setContentTitle(data.get("title"));
        builder.setContentText(data.get("body"));
        builder.setSmallIcon(R.drawable.grappbox_mini_logo);

        Intent intent = new Intent(this, DebugActivity.class);
        intent.putExtra(DebugActivity.EXTRA_DEBUG, data.get("body"));
        PendingIntent pending = PendingIntent.getActivity(this, 0, intent, PendingIntent.FLAG_CANCEL_CURRENT);
        builder.setContentIntent(pending);
        Notification notif = builder.build();
        NotificationManager nm = (NotificationManager) getSystemService(Service.NOTIFICATION_SERVICE);
        nm.notify(idNotif++, notif);
        Log.d("Test", data.get("title"));
    }
}
