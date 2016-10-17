package com.grappbox.grappbox.sync;

import android.app.Notification;
import android.app.NotificationManager;
import android.app.Service;
import android.content.Intent;
import android.os.IBinder;
import android.support.v4.app.NotificationCompat;
import android.support.v4.content.ContextCompat;
import android.util.Log;

import com.google.firebase.messaging.RemoteMessage;
import com.grappbox.grappbox.R;

import java.util.Map;

public class FirebaseMessagingService extends com.google.firebase.messaging.FirebaseMessagingService {
    public static final String TAG = FirebaseMessagingService.class.getSimpleName();

    public FirebaseMessagingService() {
    }

    @Override
    public void onMessageReceived(RemoteMessage remoteMessage) {
        // TODO(developer): Handle FCM messages here.
        // If the application is in the foreground handle both data and notification messages here.
        // Also if you intend on generating your own notifications as a result of a received FCM
        // message, here is where that should be initiated. See sendNotification method below.
        Notification.Builder builder = new Notification.Builder(this);
        Map<String, String> data = remoteMessage.getData();
        builder.setContentTitle(data.get("title"));
        builder.setContentText(data.get("body"));
        builder.setSmallIcon(R.drawable.grappbox_mini_logo);
        Notification notif = builder.build();
        NotificationManager nm = (NotificationManager) getSystemService(Service.NOTIFICATION_SERVICE);
        nm.notify(0, notif);
        Log.d("Test", data.get("title"));
    }
}
