/*
 * Created by Marc Wieser the 4/11/2016
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

import com.grappbox.grappbox.data.GrappboxContract.CustomerAccessEntry;

public class CustomerAccessModel implements Parcelable {
    public static final String[] projection = {
           CustomerAccessEntry.TABLE_NAME + "." + CustomerAccessEntry._ID,
           CustomerAccessEntry.TABLE_NAME + "." + CustomerAccessEntry.COLUMN_GRAPPBOX_ID,
           CustomerAccessEntry.TABLE_NAME + "." + CustomerAccessEntry.COLUMN_PROJECT_ID,
           CustomerAccessEntry.TABLE_NAME + "." + CustomerAccessEntry.COLUMN_NAME,
           CustomerAccessEntry.TABLE_NAME + "." + CustomerAccessEntry.COLUMN_TOKEN
    };
    public static final int _ID = 0, GRAPPBOX_ID = 1, PROJECT_ID = 2, NAME = 3, TOKEN = 4;

    public long _id, projectId;
    public String grappbox_id, name, token;

    public CustomerAccessModel(Cursor data){
        _id = data.getLong(_ID);
        projectId = data.getLong(PROJECT_ID);
        grappbox_id = data.getString(GRAPPBOX_ID);
        name = data.getString(NAME);
        token = data.getString(TOKEN);
    }

    protected CustomerAccessModel(Parcel in) {
        _id = in.readLong();
        projectId = in.readLong();
        grappbox_id = in.readString();
        name = in.readString();
        token = in.readString();
    }

    public static final Creator<CustomerAccessModel> CREATOR = new Creator<CustomerAccessModel>() {
        @Override
        public CustomerAccessModel createFromParcel(Parcel in) {
            return new CustomerAccessModel(in);
        }

        @Override
        public CustomerAccessModel[] newArray(int size) {
            return new CustomerAccessModel[size];
        }
    };

    @Override
    public int describeContents() {
        return 0;
    }

    @Override
    public void writeToParcel(Parcel dest, int flags) {
        dest.writeLong(_id);
        dest.writeLong(projectId);
        dest.writeString(grappbox_id);
        dest.writeString(name);
        dest.writeString(token);
    }
}
