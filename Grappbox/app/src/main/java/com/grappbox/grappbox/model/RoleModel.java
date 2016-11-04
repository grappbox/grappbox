/*
 * Created by Marc Wieser on 4/11/2016
 * If you have any problem or question about this work
 * please contact the author at marc.wieser@gmail.com
 *
 * The following code is owned by GrappBox you can't
 * use it without any authorization or special instructions
 * GrappBox © 2016
 */

package com.grappbox.grappbox.model;


import android.database.Cursor;
import android.os.Parcel;
import android.os.Parcelable;

import com.grappbox.grappbox.data.GrappboxContract;

public class RoleModel implements Parcelable {
    public final static String[] projection = {
            GrappboxContract.RolesEntry.TABLE_NAME + "." + GrappboxContract.RolesEntry._ID,
            GrappboxContract.RolesEntry.TABLE_NAME + "." + GrappboxContract.RolesEntry.COLUMN_GRAPPBOX_ID,
            GrappboxContract.RolesEntry.TABLE_NAME + "." + GrappboxContract.RolesEntry.COLUMN_NAME,
            GrappboxContract.RolesEntry.TABLE_NAME + "." + GrappboxContract.RolesEntry.COLUMN_ACCESS_BUGTRACKER,
            GrappboxContract.RolesEntry.TABLE_NAME + "." + GrappboxContract.RolesEntry.COLUMN_ACCESS_CLOUD,
            GrappboxContract.RolesEntry.TABLE_NAME + "." + GrappboxContract.RolesEntry.COLUMN_ACCESS_CUSTOMER_TIMELINE,
            GrappboxContract.RolesEntry.TABLE_NAME + "." + GrappboxContract.RolesEntry.COLUMN_ACCESS_TEAM_TIMELINE,
            GrappboxContract.RolesEntry.TABLE_NAME + "." + GrappboxContract.RolesEntry.COLUMN_ACCESS_EVENT,
            GrappboxContract.RolesEntry.TABLE_NAME + "." + GrappboxContract.RolesEntry.COLUMN_ACCESS_GANTT,
            GrappboxContract.RolesEntry.TABLE_NAME + "." + GrappboxContract.RolesEntry.COLUMN_ACCESS_PROJECT_SETTINGS,
            GrappboxContract.RolesEntry.TABLE_NAME + "." + GrappboxContract.RolesEntry.COLUMN_ACCESS_TASK,
            GrappboxContract.RolesEntry.TABLE_NAME + "." + GrappboxContract.RolesEntry.COLUMN_ACCESS_WHITEBOARD
    };
    public final static int _ID = 0, GRAPPBOX_ID = 1, NAME = 2, ACCESS_BUGTRACKER = 3, ACCESS_CLOUD = 4, ACCESS_CUSTOMER_TIMELINE = 5,
            ACCESS_TEAM_TIMELINE = 6, ACCESS_EVENT = 7, ACCESS_GANTT = 8, ACCESS_PROJECT_SETTINGS = 9, ACCESS_TASK = 10, ACCESS_WHITEBOARD = 11;

    public long _id;
    public String grappbox_id, name;
    public int bugtrackerAccess, cloudAccess, customerTimelineAccess, teamTimelineAccess, eventAccess, ganttAccess, projectSettingsAccess, taskAccess, whiteboardAccess;

    public RoleModel(Cursor data){
        _id = data.getLong(_ID);
        grappbox_id = data.getString(GRAPPBOX_ID);
        name = data.getString(NAME);
        bugtrackerAccess = data.getInt(ACCESS_BUGTRACKER);
        cloudAccess = data.getInt(ACCESS_CLOUD);
        customerTimelineAccess = data.getInt(ACCESS_CUSTOMER_TIMELINE);
        teamTimelineAccess = data.getInt(ACCESS_TEAM_TIMELINE);
        eventAccess = data.getInt(ACCESS_EVENT);
        ganttAccess = data.getInt(ACCESS_GANTT);
        projectSettingsAccess = data.getInt(ACCESS_PROJECT_SETTINGS);
        taskAccess = data.getInt(ACCESS_TASK);
        whiteboardAccess = data.getInt(ACCESS_WHITEBOARD);
    }

    protected RoleModel(Parcel in) {
        _id = in.readLong();
        grappbox_id = in.readString();
        name = in.readString();
        bugtrackerAccess = in.readInt();
        cloudAccess = in.readInt();
        customerTimelineAccess = in.readInt();
        teamTimelineAccess = in.readInt();
        eventAccess = in.readInt();
        ganttAccess = in.readInt();
        projectSettingsAccess = in.readInt();
        taskAccess = in.readInt();
        whiteboardAccess = in.readInt();
    }

    public static final Creator<RoleModel> CREATOR = new Creator<RoleModel>() {
        @Override
        public RoleModel createFromParcel(Parcel in) {
            return new RoleModel(in);
        }

        @Override
        public RoleModel[] newArray(int size) {
            return new RoleModel[size];
        }
    };

    @Override
    public int describeContents() {
        return 0;
    }

    @Override
    public void writeToParcel(Parcel dest, int flags) {
        dest.writeLong(_id);
        dest.writeString(grappbox_id);
        dest.writeString(name);
        dest.writeInt(bugtrackerAccess);
        dest.writeInt(cloudAccess);
        dest.writeInt(customerTimelineAccess);
        dest.writeInt(teamTimelineAccess);
        dest.writeInt(eventAccess);
        dest.writeInt(ganttAccess);
        dest.writeInt(projectSettingsAccess);
        dest.writeInt(taskAccess);
        dest.writeInt(whiteboardAccess);
    }

    public static String[] toStringArray(RoleModel[] arr){
        String[] strs = new String[arr.length];
        for (int i = 0; i < arr.length; ++i){
            strs[i] = arr[i].name;
        }
        return strs;
    }
}
