/*
 * Created by Marc Wieser on 17/11/2016
 * If you have any problem or question about this work
 * please contact the author at marc.wieser@gmail.com
 *
 * The following code is owned by GrappBox you can't
 * use it without any authorization or special instructions
 * GrappBox © 2016
 */

package com.grappbox.grappbox.messaging;


import android.content.ContentValues;
import android.content.Context;
import android.content.Intent;
import android.content.OperationApplicationException;
import android.database.Cursor;

import com.grappbox.grappbox.Utils;
import com.grappbox.grappbox.data.GrappboxContract;
import com.grappbox.grappbox.data.GrappboxContract.ProjectEntry;
import com.grappbox.grappbox.interfaces.MessagingDispatcher;
import com.grappbox.grappbox.sync.GrappboxJustInTimeService;

import org.json.JSONException;
import org.json.JSONObject;

import java.text.ParseException;
import java.util.HashMap;
import java.util.Map;

class ProjectMessagingDispatcher implements MessagingDispatcher {
    Context mContext;
    private Map<String, MessagingDispatcher> mDispatcher;

    ProjectMessagingDispatcher(Context context){
        mContext = context;
        mDispatcher = new HashMap<>();
        mDispatcher.put("new project", new HandleNew());
        mDispatcher.put("update project", new HandleUpdate());
        mDispatcher.put("delete project", new HandleDelete());
        mDispatcher.put("retrieve project", new HandleRetrieve());
        mDispatcher.put("new customeraccess", new HandleNewCustomerAccess());
        mDispatcher.put("delete customeraccess", new HandleDeleteCustomerAccess());
        mDispatcher.put("user assign project", new HandleUserAssign());
        mDispatcher.put("user unassign project", new HandleUserUnAssign());
    }

    @Override
    public void dispatch(String action, JSONObject body) {
        mDispatcher.get(action).dispatch(action, body);
    }

    private class HandleNew implements MessagingDispatcher{

        @Override
        public void dispatch(String action, JSONObject body) {
            new HandleUpdate().dispatch(action, body);
        }
    }

    private class HandleUpdate implements MessagingDispatcher{

        @Override
        public void dispatch(String action, JSONObject body) {
            ContentValues values = new ContentValues();
            Cursor creator = null;

            try {
                creator = mContext.getContentResolver().query(GrappboxContract.UserEntry.CONTENT_URI, new String[]{GrappboxContract.UserEntry._ID}, GrappboxContract.UserEntry.COLUMN_GRAPPBOX_ID+"=?", new String[]{body.getJSONObject("creator").getString("id")}, null);
                if (creator == null || !creator.moveToFirst())
                    throw new OperationApplicationException(Utils.Errors.ERROR_INVALID_ID);
                values.put(ProjectEntry.COLUMN_GRAPPBOX_ID, body.getString("id"));
                values.put(ProjectEntry.COLUMN_NAME, body.getString("name"));
                values.put(ProjectEntry.COLUMN_DESCRIPTION, body.getString("description"));
                values.put(ProjectEntry.COLUMN_LOCAL_CREATOR_ID, creator.getLong(0));
                values.put(ProjectEntry.COLUMN_CONTACT_PHONE, body.getString("phone"));
                values.put(ProjectEntry.COLUMN_COMPANY_NAME, body.getString("company"));
                values.put(ProjectEntry.COLUMN_CONTACT_EMAIL, body.getString("contact_mail"));
                values.put(ProjectEntry.COLUMN_SOCIAL_FACEBOOK, body.getString("facebook"));
                values.put(ProjectEntry.COLUMN_SOCIAL_TWITTER, body.getString("twitter"));
                String color = body.getString("color");
                color = color.startsWith("#") ? color : "#" + color;
                values.put(ProjectEntry.COLUMN_COLOR, color);
                values.put(ProjectEntry.COLUMN_DATE_LOGO_LAST_EDITED_UTC, Utils.Date.getDateFromGrappboxAPIToUTC(body.getString("created_at")));
                if (body.isNull("deleted_at"))
                    values.putNull(ProjectEntry.COLUMN_DATE_DELETED_UTC);
                else
                    values.put(ProjectEntry.COLUMN_DATE_DELETED_UTC, Utils.Date.getDateFromGrappboxAPIToUTC(body.getString("deleted_at")));
                mContext.getContentResolver().insert(ProjectEntry.CONTENT_URI, values);
            } catch (JSONException | OperationApplicationException | ParseException e) {
                e.printStackTrace();
            } finally {
                if (creator != null)
                    creator.close();
            }
        }
    }

    private class HandleDelete implements MessagingDispatcher{

        @Override
        public void dispatch(String action, JSONObject body) {
            try {
                ContentValues values = new ContentValues();

                values.put(ProjectEntry.COLUMN_GRAPPBOX_ID, body.getString("id"));
                values.put(ProjectEntry.COLUMN_DATE_DELETED_UTC, Utils.Date.nowUTC());
                mContext.getContentResolver().insert(ProjectEntry.CONTENT_URI, values);
            } catch (JSONException | ParseException e) {
                e.printStackTrace();
            }
        }
    }

    private class HandleRetrieve implements MessagingDispatcher{

        @Override
        public void dispatch(String action, JSONObject body) {
            try {
                ContentValues values = new ContentValues();

                values.put(ProjectEntry.COLUMN_GRAPPBOX_ID, body.getString("id"));
                values.putNull(ProjectEntry.COLUMN_DATE_DELETED_UTC);
                mContext.getContentResolver().insert(ProjectEntry.CONTENT_URI, values);
            } catch (JSONException e) {
                e.printStackTrace();
            }
        }
    }

    private class HandleNewCustomerAccess implements MessagingDispatcher{

        @Override
        public void dispatch(String action, JSONObject body) {
            Cursor project = null;
            ContentValues values = new ContentValues();
            try {
                project = mContext.getContentResolver().query(ProjectEntry.CONTENT_URI, new String[]{ProjectEntry._ID}, ProjectEntry.COLUMN_GRAPPBOX_ID+"=?", new String[]{body.getString("project_id")}, null);
                if (project == null || !project.moveToFirst())
                    throw new OperationApplicationException(Utils.Errors.ERROR_INVALID_ID);
                values.put(GrappboxContract.CustomerAccessEntry.COLUMN_GRAPPBOX_ID, body.getString("id"));
                values.put(GrappboxContract.CustomerAccessEntry.COLUMN_PROJECT_ID, project.getLong(0));
                values.put(GrappboxContract.CustomerAccessEntry.COLUMN_NAME, body.getString("name"));
                values.put(GrappboxContract.CustomerAccessEntry.COLUMN_TOKEN, body.getString("token"));
                mContext.getContentResolver().insert(GrappboxContract.CustomerAccessEntry.CONTENT_URI, values);
            } catch (JSONException | OperationApplicationException e) {
                e.printStackTrace();
            } finally {
                if (project != null)
                    project.close();
            }
        }
    }

    private class HandleDeleteCustomerAccess implements MessagingDispatcher{

        @Override
        public void dispatch(String action, JSONObject body) {
            try {
                mContext.getContentResolver().delete(GrappboxContract.CustomerAccessEntry.CONTENT_URI, GrappboxContract.CustomerAccessEntry.COLUMN_GRAPPBOX_ID+"=?", new String[]{body.getString("id")});
            } catch (JSONException e) {
                e.printStackTrace();
            }
        }
    }

    private class HandleUserAssign implements MessagingDispatcher{

        @Override
        public void dispatch(String action, JSONObject body) {
            Intent userDetail = new Intent(mContext, GrappboxJustInTimeService.class);

            try {
                userDetail.setAction(GrappboxJustInTimeService.ACTION_SYNC_USER_DETAIL);
                userDetail.putExtra(GrappboxJustInTimeService.EXTRA_USER_ID, body.getJSONObject("user").getString("id"));
                mContext.startService(userDetail);
            } catch (JSONException e) {
                e.printStackTrace();
            }
            Cursor user = null;

            try {
                user = mContext.getContentResolver().query(GrappboxContract.UserEntry.CONTENT_URI, new String[]{GrappboxContract.UserEntry._ID}, GrappboxContract.UserEntry.COLUMN_GRAPPBOX_ID+"=?", new String[]{body.getJSONObject("user").getString("id")}, null);
                if (user == null || !user.moveToFirst())
                    throw new OperationApplicationException(Utils.Errors.ERROR_INVALID_ID);

            } catch (JSONException | OperationApplicationException e) {
                e.printStackTrace();
            } finally {
                if (user != null)
                    user.close();
            }
        }
    }

    private class HandleUserUnAssign implements MessagingDispatcher{

        @Override
        public void dispatch(String action, JSONObject body) {
            Cursor roles = null;

            try {
                roles = mContext.getContentResolver().query(GrappboxContract.RolesAssignationEntry.buildRoleAssignationWithUIDAndPID(), new String[]{GrappboxContract.RolesAssignationEntry.TABLE_NAME + "."+ GrappboxContract.RolesAssignationEntry._ID}, ProjectEntry.TABLE_NAME + "." + ProjectEntry.COLUMN_GRAPPBOX_ID+"=? AND " + GrappboxContract.UserEntry.TABLE_NAME + "." + GrappboxContract.UserEntry.COLUMN_GRAPPBOX_ID + "=?", new String[]{body.getString("id"), body.getJSONObject("user").getString("id")}, null);
                if (roles == null || !roles.moveToFirst())
                    throw new OperationApplicationException(Utils.Errors.ERROR_INVALID_ID);
                String selection = "";
                do {
                    if (selection.isEmpty())
                        selection += GrappboxContract.RolesAssignationEntry._ID + " IN (";
                    else
                        selection += ", ";
                    selection += String.valueOf(roles.getLong(0));
                } while (roles.moveToNext());
                selection += ")";
                mContext.getContentResolver().delete(GrappboxContract.RolesAssignationEntry.CONTENT_URI, selection, null);
            } catch (JSONException | OperationApplicationException e) {
                e.printStackTrace();
            } finally {
                if (roles != null)
                    roles.close();
            }
        }
    }
}
