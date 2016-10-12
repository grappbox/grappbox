package com.grappbox.grappbox.model;

import android.database.Cursor;
import android.os.Parcel;
import android.os.Parcelable;

import com.grappbox.grappbox.data.GrappboxContract;

/**
 * Created by tan_f on 11/10/2016.
 */

public class TimelineMessageCommentModel implements Parcelable {

    public String   _id;
    public String   _parentId;
    public String   _title;
    public String   _comment;
    public String   _lastupdate;

    public TimelineMessageCommentModel(Cursor data){
        _id = data.getString(data.getColumnIndex(GrappboxContract.TimelineMessageEntry._ID));
        _parentId = data.getString(data.getColumnIndex(GrappboxContract.TimelineMessageEntry.COLUMN_PARENT_ID));
        _title = data.getString(data.getColumnIndex(GrappboxContract.TimelineMessageEntry.COLUMN_TITLE));
        _comment = data.getString(data.getColumnIndex(GrappboxContract.TimelineMessageEntry.COLUMN_MESSAGE));
        _lastupdate = data.getString(data.getColumnIndex(GrappboxContract.TimelineMessageEntry.COLUMN_DATE_LAST_EDITED_AT_UTC));

    }

    protected TimelineMessageCommentModel(Parcel source){
        _id = source.readString();
        _parentId = source.readString();
        _title = source.readString();
        _comment = source.readString();
        _lastupdate = source.readString();
    }

    public static final Creator<TimelineMessageCommentModel> CREATOR = new Creator<TimelineMessageCommentModel>() {
        @Override
        public TimelineMessageCommentModel createFromParcel(Parcel source) {
            return new TimelineMessageCommentModel(source);
        }

        @Override
        public TimelineMessageCommentModel[] newArray(int size) {
            return new TimelineMessageCommentModel[size];
        }
    };

    @Override
    public int describeContents() {
        return 0;
    }

    @Override
    public void writeToParcel(Parcel dest, int flags) {
        dest.writeString(_id);
        dest.writeString(_parentId);
        dest.writeString(_title);
        dest.writeString(_comment);
        dest.writeString(_lastupdate);
    }
}
