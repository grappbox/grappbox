package com.grappbox.grappbox.model;

import android.content.Context;
import android.database.Cursor;
import android.database.DatabaseUtils;
import android.os.Parcel;
import android.os.Parcelable;
import android.util.Log;

import com.grappbox.grappbox.R;
import com.grappbox.grappbox.Utils;
import com.grappbox.grappbox.data.GrappboxContract;

import java.text.DateFormat;
import java.text.ParseException;
import java.util.ArrayList;
import java.util.Arrays;
import java.util.List;
import java.util.Locale;

/**
 * Created by marc on 30/09/2016.
 */

public class BugModel implements Parcelable {
    private static final String LOG_TAG = BugModel.class.getSimpleName();
    public long _id = -1;
    public String grappboxId, title, date, desc;
    public boolean isClosed;

    /*
        The following data are considered as additional data.
        It's recommended to lazy load it.
     */
    public long projectID = -1;
    public long assigneeCount, commentsCount;
    public List<BugTagModel> tags;
    public List<UserModel> assignees;
    public List<BugCommentModel> comments;

    public BugModel(Context context, Cursor cursor){
        isClosed = cursor.getString(cursor.getColumnIndex(GrappboxContract.BugEntry.COLUMN_DATE_DELETED_UTC)) != null && !cursor.getString(cursor.getColumnIndex(GrappboxContract.BugEntry.COLUMN_DATE_DELETED_UTC)).isEmpty();
        _id = cursor.getLong(cursor.getColumnIndex(GrappboxContract.BugEntry._ID));
        grappboxId = cursor.getString(cursor.getColumnIndex(GrappboxContract.BugEntry.COLUMN_GRAPPBOX_ID));
        title = cursor.getString(cursor.getColumnIndex(GrappboxContract.BugEntry.COLUMN_TITLE));
        try {
            date = context.getString(R.string.bug_status_date, context.getString(isClosed ? R.string.bug_status_closed : R.string.bug_status_opened), DateFormat.getDateInstance(DateFormat.SHORT, Locale.getDefault()).format(Utils.Date.convertUTCToPhone(cursor.getString(cursor.getColumnIndex(isClosed ? GrappboxContract.BugEntry.COLUMN_DATE_DELETED_UTC : GrappboxContract.BugEntry.COLUMN_DATE_LAST_EDITED_UTC)))));
            desc = cursor.getString(cursor.getColumnIndex(GrappboxContract.BugEntry.COLUMN_DESCRIPTION));
        } catch (ParseException e) {
            e.printStackTrace();
            date = context.getString(R.string.error_unknown_last_modified);
        }
        assigneeCount = 0;
        commentsCount = 0;
        tags = new ArrayList<>();
        assignees = new ArrayList<>();
        comments = new ArrayList<>();
    }

    protected BugModel(Parcel in) {
        _id = in.readLong();
        grappboxId = in.readString();
        title = in.readString();
        date = in.readString();
        desc = in.readString();
        assigneeCount = in.readLong();
        commentsCount = in.readLong();
        Parcelable[] arrTags = in.readParcelableArray(BugTagModel.class.getClassLoader());
        Log.e(LOG_TAG, "Tag model in parcel : " + arrTags.length);
        tags = new ArrayList<>();
        for (Parcelable tag : arrTags){
            tags.add((BugTagModel) tag);
        }
        Parcelable[] arrAssignee = in.readParcelableArray(UserModel.class.getClassLoader());
        assignees = new ArrayList<>();
        for (Parcelable ass : arrAssignee){
            assignees.add((UserModel) ass);
        }
        Parcelable[] arrComm = in.readParcelableArray(BugCommentModel.class.getClassLoader());
        comments = new ArrayList<>();
        for (Parcelable com : arrComm){
            comments.add((BugCommentModel) com);
        }
        projectID = in.readLong();
    }

    public void setCoreData(Context context, Cursor cursor){
        isClosed = cursor.getString(cursor.getColumnIndex(GrappboxContract.BugEntry.COLUMN_DATE_DELETED_UTC)) != null && !cursor.getString(cursor.getColumnIndex(GrappboxContract.BugEntry.COLUMN_DATE_DELETED_UTC)).isEmpty();
        _id = cursor.getLong(cursor.getColumnIndex(GrappboxContract.BugEntry._ID));
        grappboxId = cursor.getString(cursor.getColumnIndex(GrappboxContract.BugEntry.COLUMN_GRAPPBOX_ID));
        title = cursor.getString(cursor.getColumnIndex(GrappboxContract.BugEntry.COLUMN_TITLE));
        try {
            date = context.getString(R.string.bug_status_date, context.getString(isClosed ? R.string.bug_status_closed : R.string.bug_status_opened), DateFormat.getDateInstance(DateFormat.SHORT, Locale.getDefault()).format(Utils.Date.convertUTCToPhone(cursor.getString(cursor.getColumnIndex(isClosed ? GrappboxContract.BugEntry.COLUMN_DATE_DELETED_UTC : GrappboxContract.BugEntry.COLUMN_DATE_LAST_EDITED_UTC)))));
            desc = cursor.getString(cursor.getColumnIndex(GrappboxContract.BugEntry.COLUMN_DESCRIPTION));
        } catch (ParseException e) {
            e.printStackTrace();
            date = context.getString(R.string.error_unknown_last_modified);
        }
    }

    public void setProjectID(long projectID){
        this.projectID = projectID;
    }

    public void setAssigneesData(List<UserModel> assigneesData){
        assignees = assigneesData;
        assigneeCount = assignees == null ? 0 : assignees.size();
    }

    public void setCommentsData(List<BugCommentModel> commentsData){
        comments = commentsData;
        this.commentsCount = commentsData == null ? 0 : commentsData.size();
    }

    public void setTagsData(List<BugTagModel> tags){
        this.tags = tags;
    }

    @Override
    public int describeContents() {
        return 0;
    }

    @Override
    public void writeToParcel(Parcel dest, int flags) {
        dest.writeLong(_id);
        dest.writeString(grappboxId);
        dest.writeString(title);
        dest.writeString(date);
        dest.writeString(desc);
        dest.writeLong(assigneeCount);
        dest.writeLong(commentsCount);
        dest.writeParcelableArray(tags.toArray(new BugTagModel[tags.size()]), 0);
        dest.writeParcelableArray(assignees.toArray(new UserModel[assignees.size()]), 0);
        dest.writeParcelableArray(comments.toArray(new BugCommentModel[comments.size()]), 0);
        dest.writeLong(projectID);
    }

    public static final Creator<BugModel> CREATOR = new Creator<BugModel>() {
        @Override
        public BugModel createFromParcel(Parcel in) {
            return new BugModel(in);
        }

        @Override
        public BugModel[] newArray(int size) {
            return new BugModel[size];
        }
    };
}
