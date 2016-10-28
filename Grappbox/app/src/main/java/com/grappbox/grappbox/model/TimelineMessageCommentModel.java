package com.grappbox.grappbox.model;

import android.content.Context;
import android.database.Cursor;
import android.os.Parcel;
import android.os.Parcelable;
import android.util.Log;

import com.grappbox.grappbox.R;
import com.grappbox.grappbox.Utils;
import com.grappbox.grappbox.data.GrappboxContract;

import java.text.DateFormat;
import java.text.ParseException;
import java.util.Locale;

/**
 * Created by tan_f on 11/10/2016.
 */

public class TimelineMessageCommentModel implements Parcelable {

    public String   _id;
    public String   _grappboxId;
    public String   _parentId;
    public String   _createId;
    public String   _comment;
    public String   _lastupdate;

    public TimelineMessageCommentModel(Context context, Cursor data){
        _id = data.getString(data.getColumnIndex(GrappboxContract.TimelineCommentEntry._ID));
        _grappboxId = data.getString(data.getColumnIndex(GrappboxContract.TimelineCommentEntry.COLUMN_GRAPPBOX_ID));
        _parentId = data.getString(data.getColumnIndex(GrappboxContract.TimelineCommentEntry.COLUMN_PARENT_ID));
        _createId = data.getString(data.getColumnIndex(GrappboxContract.TimelineCommentEntry.COLUMN_LOCAL_CREATOR_ID));
        _comment = data.getString(data.getColumnIndex(GrappboxContract.TimelineCommentEntry.COLUMN_MESSAGE));
        _lastupdate = data.getString(data.getColumnIndex(GrappboxContract.TimelineMessageEntry.COLUMN_DATE_LAST_EDITED_AT_UTC));
    }

    protected TimelineMessageCommentModel(Parcel source){
        _id = source.readString();
        _grappboxId = source.readString();
        _parentId = source.readString();
        _createId = source.readString();
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
        dest.writeString(_grappboxId);
        dest.writeString(_parentId);
        dest.writeString(_createId);
        dest.writeString(_comment);
        dest.writeString(_lastupdate);
    }
}
