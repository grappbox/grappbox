package com.grappbox.grappbox.messaging;


import android.content.Context;

import com.grappbox.grappbox.WhiteboardDrawingActivity;
import com.grappbox.grappbox.WhiteboardDrawingActivityFragment;
import com.grappbox.grappbox.interfaces.MessagingDispatcher;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.util.HashMap;
import java.util.Map;

class WhiteboardMessagingDispatcher implements MessagingDispatcher {
    Context mContext;
    private Map<String, MessagingDispatcher> mDispatcher;

    WhiteboardMessagingDispatcher(Context context) {
        mContext = context;
        mDispatcher = new HashMap<>();
        mDispatcher.put("new whiteboard", new HandleNew());
        mDispatcher.put("delete whiteboard", new HandleDelete());
        mDispatcher.put("new object", new HandleNewObject());
        mDispatcher.put("delete object", new HandleDeleteObject());
        mDispatcher.put("login whiteboard", new HandleLogin());
        mDispatcher.put("logout whiteboard", new HandleLogout());
    }

    @Override
    public void dispatch(String action, JSONObject body) {
        mDispatcher.get(action).dispatch(action, body);
    }

    private class HandleNew implements MessagingDispatcher{

        @Override
        public void dispatch(String action, JSONObject body) {

        }
    }

    private class HandleDelete implements MessagingDispatcher{

        @Override
        public void dispatch(String action, JSONObject body) {
            try {
                WhiteboardDrawingActivityFragment.mDrawingArea.deleteObject(body.getString("id"));
            } catch (JSONException e) {
                e.printStackTrace();
            }
        }
    }

    private class HandleNewObject implements MessagingDispatcher{

        @Override
        public void dispatch(String action, JSONObject body) {
            if (WhiteboardDrawingActivityFragment.mDrawingArea == null)
                return;

            try {
                JSONObject object = body.getJSONObject("object");
                object.put("id", body.getString("id"));
                JSONArray arr = new JSONArray();
                arr.put(object);
                WhiteboardDrawingActivityFragment.mDrawingArea.feed(arr);
            } catch (JSONException e) {
                e.printStackTrace();
            }
        }
    }

    private class HandleDeleteObject implements MessagingDispatcher{

        @Override
        public void dispatch(String action, JSONObject body) {
            if (WhiteboardDrawingActivityFragment.mDrawingArea != null)
            {
                try {
                    WhiteboardDrawingActivityFragment.mDrawingArea.deleteObject(body.getString("id"));
                } catch (JSONException e) {
                    e.printStackTrace();
                }
            }
        }
    }

    private class HandleLogin implements MessagingDispatcher{

        @Override
        public void dispatch(String action, JSONObject body) {

        }
    }

    private class HandleLogout implements MessagingDispatcher{

        @Override
        public void dispatch(String action, JSONObject body) {

        }
    }

}
