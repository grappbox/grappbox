/*
 * Created by Marc Wieser on 11/11/2016
 * If you have any problem or question about this work
 * please contact the author at marc.wieser@gmail.com
 *
 * The following code is owned by GrappBox you can't
 * use it without any authorization or special instructions
 * GrappBox © 2016
 */

package com.grappbox.grappbox.sync;


import com.grappbox.grappbox.interfaces.MessagingDispatcher;

import org.json.JSONObject;

import java.util.HashMap;
import java.util.Map;

public class EventMessagingDispatcher implements MessagingDispatcher {

    private Map<String, MessagingDispatcher> mDispatcher;

    EventMessagingDispatcher(){
        mDispatcher = new HashMap<>();
        mDispatcher.put("new event", new HandleNew());
        mDispatcher.put("update event", new HandleUpdate());
        mDispatcher.put("delete event", new HandleDelete());
        mDispatcher.put("participants event", new HandleParticipants());
    }

    @Override
    public void dispatch(String action, JSONObject body) {
        mDispatcher.get(action).dispatch(action, body);
    }

    private static class HandleNew implements MessagingDispatcher{

        @Override
        public void dispatch(String action, JSONObject body) {

        }
    }

    private static class HandleUpdate implements MessagingDispatcher{

        @Override
        public void dispatch(String action, JSONObject body) {

        }
    }

    private static class HandleDelete implements MessagingDispatcher{

        @Override
        public void dispatch(String action, JSONObject body) {

        }
    }

    private static class HandleParticipants implements MessagingDispatcher{

        @Override
        public void dispatch(String action, JSONObject body) {

        }
    }
}
