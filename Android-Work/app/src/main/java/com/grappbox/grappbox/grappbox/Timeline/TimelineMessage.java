package com.grappbox.grappbox.grappbox.Timeline;

import android.content.ContentValues;
import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;


import com.grappbox.grappbox.grappbox.R;

import java.util.List;

/**
 * Created by tan_f on 27/02/2016.
 */
public class TimelineMessage extends Fragment {

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        return inflater.inflate(R.layout.fragment_list_comment_timeline, container, false);
    }

    public void fillView(List<ContentValues> listMessage)
    {

    }
}
