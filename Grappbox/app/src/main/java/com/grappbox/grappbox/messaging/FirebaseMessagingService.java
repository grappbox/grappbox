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

import android.app.Notification;
import android.app.NotificationManager;
import android.app.PendingIntent;
import android.app.Service;
import android.content.Context;
import android.content.Intent;

import com.google.firebase.messaging.RemoteMessage;
import com.grappbox.grappbox.DebugActivity;
import com.grappbox.grappbox.R;
import com.grappbox.grappbox.interfaces.MessagingDispatcher;

import org.json.JSONException;
import org.json.JSONObject;

import java.util.HashMap;
import java.util.Map;

/**
 * Created by Marc Wieser the 21/10/2016.
 * If you have any problem or question about this work
 * please contact the author at marc.wieser33@gmail.com
 *
 * The following code is owned by GrappBox you can't
 * use it without any authorization or special instructions
 * GrappBox © 2016
 */

public class FirebaseMessagingService extends com.google.firebase.messaging.FirebaseMessagingService {
    public static final String TAG = FirebaseMessagingService.class.getSimpleName();
    private MessagingDispatcher mMainDispatcher;
    static int idNotif = 0;

    public FirebaseMessagingService() {
        mMainDispatcher = new MainDispatcher(this);
    }

    @Override
    public void onMessageReceived(RemoteMessage remoteMessage) {
        Map<String, String> data = remoteMessage.getData();
        try {
            String action = data.get("title");
            JSONObject body = new JSONObject(data.get("body"));
            mMainDispatcher.dispatch(action, body);
        } catch (JSONException e) {
            e.printStackTrace();
        }

        //The following code is a debug code to show API sended messages
        //TODO : delete it
        Notification.Builder builder = new Notification.Builder(this);
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
    }

    private class MainDispatcher implements MessagingDispatcher{
        private Context mContext;
        private Map<String, MessagingDispatcher> mDispatcher;

        MainDispatcher(Context context){
            mContext = context;
            MessagingDispatcher projectDispatcher = new ProjectMessagingDispatcher(mContext);
            MessagingDispatcher whiteboardDispatcher = new WhiteboardMessagingDispatcher(mContext);
            mDispatcher = new HashMap<>();
            mDispatcher.put("bug", new BugMessagingDispatcher(mContext));
            mDispatcher.put("event", new EventMessagingDispatcher(mContext));
            mDispatcher.put("project", projectDispatcher);
            mDispatcher.put("customeraccess", projectDispatcher);
            mDispatcher.put("message", new TimelineMessageDispatcher(mContext));
            mDispatcher.put("role", new RoleMessagingDispatcher(mContext));
            mDispatcher.put("task", new TaskMessagingDispatcher(mContext));
            mDispatcher.put("whiteboard", whiteboardDispatcher);
            mDispatcher.put("object", whiteboardDispatcher);
        }

        @Override
        public void dispatch(String action, JSONObject body) {
            String[] splittedAction = action.split(" ");
            String actionType = splittedAction[splittedAction.length - 1];
            mDispatcher.get(actionType).dispatch(action, body);
        }
    }
}
