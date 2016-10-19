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
import java.util.ArrayList;
import java.util.List;
import java.util.Locale;

/**
 * Created by tan_f on 10/10/2016.
 */

public class TimelineModel implements Parcelable {

    public int      _id;
    public int      _countAnswer;
    public long     _timelineType;
    public String   _grappboxId;
    public String   _title;
    public String   _message;
    public String   _lastUpadte;
    public String   _createID;



    public TimelineModel(Context context, Cursor cursor){
        _id = cursor.getInt(cursor.getColumnIndex(GrappboxContract.TimelineMessageEntry._ID));
        _countAnswer = cursor.getInt(cursor.getColumnIndex(GrappboxContract.TimelineMessageEntry.COLUMN_COUNT_ANSWER));
        _timelineType = cursor.getLong(cursor.getColumnIndex(GrappboxContract.TimelineEntry.COLUMN_TYPE_ID));
        _grappboxId = cursor.getString(cursor.getColumnIndex(GrappboxContract.TimelineMessageEntry.COLUMN_GRAPPBOX_ID));
        _title = cursor.getString(cursor.getColumnIndex(GrappboxContract.TimelineMessageEntry.COLUMN_TITLE));
        _message = cursor.getString(cursor.getColumnIndex(GrappboxContract.TimelineMessageEntry.COLUMN_MESSAGE));
        try {
            _lastUpadte = DateFormat.getDateInstance(DateFormat.MEDIUM, Locale.getDefault()).format(Utils.Date.convertUTCToPhone(cursor.getString(cursor.getColumnIndex(GrappboxContract.TimelineMessageEntry.COLUMN_DATE_LAST_EDITED_AT_UTC))));
        } catch (ParseException e) {
            e.printStackTrace();
            _lastUpadte = context.getString(R.string.error_unknown_last_modified);
        }
        _createID = cursor.getString(cursor.getColumnIndex(GrappboxContract.TimelineMessageEntry.COLUMN_LOCAL_CREATOR_ID));
    }

    public TimelineModel(Parcel source){
        _id = source.readInt();
        _countAnswer = source.readInt();
        _timelineType = source.readLong();
        _grappboxId = source.readString();
        _title = source.readString();
        _message = source.readString();
        _lastUpadte = source.readString();
        _createID = source.readString();
    }

    @Override
    public void writeToParcel(Parcel dest, int flags) {
        dest.writeInt(_id);
        dest.writeInt(_countAnswer);
        dest.writeLong(_timelineType);
        dest.writeString(_grappboxId);
        dest.writeString(_title);
        dest.writeString(_message);
        dest.writeString(_lastUpadte);
        dest.writeString(_createID);
    }

    @Override
    public int describeContents() {
        return 0;
    }

    public static final Parcelable.Creator<TimelineModel> CREATOR = new Parcelable.Creator<TimelineModel>() {
        @Override
        public TimelineModel createFromParcel(Parcel source) {
            return new TimelineModel(source);
        }

        @Override
        public TimelineModel[] newArray(int size) {
            return new TimelineModel[size];
        }
    };
}
