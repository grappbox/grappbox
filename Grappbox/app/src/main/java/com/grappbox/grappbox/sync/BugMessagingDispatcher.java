/*
 * Created by Marc Wieser on 10/11/2016
 * If you have any problem or question about this work
 * please contact the author at marc.wieser@gmail.com
 *
 * The following code is owned by GrappBox you can't
 * use it without any authorization or special instructions
 * GrappBox © 2016
 */

package com.grappbox.grappbox.sync;


import org.json.JSONObject;

import java.util.Map;

public class BugMessagingDispatcher implements MessagingDispatcher {
    private static Map<String, MessagingDispatcher> mMessagingProcessing;

    static{
        MessagingDispatcher handleNew = new HandleNew();
        MessagingDispatcher handleUpdate = new HandleUpdate();
        MessagingDispatcher handleClose = new HandleClose();
        MessagingDispatcher handleReopen = new HandleReopen();
        MessagingDispatcher handleDelete = new HandleDelete();
        MessagingDispatcher handleParticipant = new HandleParticipant();
        MessagingDispatcher handleNewComment = new HandleNewComment();
        MessagingDispatcher handleEditComment = new HandleEditComment();
        MessagingDispatcher handleDeleteComment = new HandleDeleteComment();
        MessagingDispatcher handleNewTag = new HandleNewTag();
        MessagingDispatcher handleUpdateTag = new HandleUpdateTag();
        MessagingDispatcher handleDeleteTag = new HandleDeleteTag();
        MessagingDispatcher handleAssignTag = new HandleAssignTag();
        MessagingDispatcher handleRemoveTag = new HandleRemoveTag();

        mMessagingProcessing.put("new bug", handleNew);
        mMessagingProcessing.put("update bug", handleUpdate);
        mMessagingProcessing.put("close bug", handleClose);
        mMessagingProcessing.put("reopen bug", handleReopen);
        mMessagingProcessing.put("delete bug", handleDelete);
        mMessagingProcessing.put("participants bug", handleParticipant);
        mMessagingProcessing.put("new comment bug", handleNewComment);
        mMessagingProcessing.put("edit comment bug", handleEditComment);
        mMessagingProcessing.put("delete comment bug", handleDeleteComment);
        mMessagingProcessing.put("new tag bug", handleNewTag);
        mMessagingProcessing.put("update tag bug", handleUpdateTag);
        mMessagingProcessing.put("delete tag bug", handleDeleteTag);
        mMessagingProcessing.put("assign tag bug", handleAssignTag);
        mMessagingProcessing.put("remove tag bug", handleRemoveTag);
    }

    @Override
    public void dispatch(String action, JSONObject body) {
        mMessagingProcessing.get(action).dispatch(action, body);
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

    private static class HandleClose implements MessagingDispatcher{

        @Override
        public void dispatch(String action, JSONObject body) {

        }
    }

    private static class HandleReopen implements MessagingDispatcher{

        @Override
        public void dispatch(String action, JSONObject body) {

        }
    }

    private static class HandleDelete implements MessagingDispatcher{

        @Override
        public void dispatch(String action, JSONObject body) {

        }
    }

    private static class HandleParticipant implements MessagingDispatcher{

        @Override
        public void dispatch(String action, JSONObject body) {

        }
    }

    private static class HandleNewComment implements MessagingDispatcher{

        @Override
        public void dispatch(String action, JSONObject body) {

        }
    }

    private static class HandleEditComment implements MessagingDispatcher{

        @Override
        public void dispatch(String action, JSONObject body) {

        }
    }

    private static class HandleDeleteComment implements MessagingDispatcher{

        @Override
        public void dispatch(String action, JSONObject body) {

        }
    }

    private static class HandleNewTag implements MessagingDispatcher{

        @Override
        public void dispatch(String action, JSONObject body) {

        }
    }

    private static class HandleUpdateTag implements MessagingDispatcher{

        @Override
        public void dispatch(String action, JSONObject body) {

        }
    }

    private static class HandleDeleteTag implements MessagingDispatcher{

        @Override
        public void dispatch(String action, JSONObject body) {

        }
    }

    private static class HandleAssignTag implements MessagingDispatcher{

        @Override
        public void dispatch(String action, JSONObject body) {

        }
    }

    private static class HandleRemoveTag implements MessagingDispatcher{

        @Override
        public void dispatch(String action, JSONObject body) {

        }
    }
}
