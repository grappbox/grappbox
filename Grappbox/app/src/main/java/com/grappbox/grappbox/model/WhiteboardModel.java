package com.grappbox.grappbox.model;


import android.database.Cursor;
import android.os.Parcel;
import android.os.Parcelable;

import org.json.JSONException;
import org.json.JSONObject;

public class WhiteboardModel implements Parcelable {
    public String grappboxId;
    public String name;
    public UserModel creator;

    public WhiteboardModel(JSONObject json, Cursor user) throws JSONException {
        creator = new UserModel(user);
        grappboxId = json.getString("id");
        name = json.getString("name");
    }

    protected WhiteboardModel(Parcel in) {
        grappboxId = in.readString();
        name = in.readString();
        creator = in.readParcelable(UserModel.class.getClassLoader());
    }

    public static final Creator<WhiteboardModel> CREATOR = new Creator<WhiteboardModel>() {
        @Override
        public WhiteboardModel createFromParcel(Parcel in) {
            return new WhiteboardModel(in);
        }

        @Override
        public WhiteboardModel[] newArray(int size) {
            return new WhiteboardModel[size];
        }
    };

    @Override
    public int describeContents() {
        return 0;
    }

    @Override
    public void writeToParcel(Parcel dest, int flags) {
        dest.writeString(grappboxId);
        dest.writeString(name);
        dest.writeParcelable(creator, flags);
    }
}
