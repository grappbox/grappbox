package com.grappbox.grappbox;

import android.app.Fragment;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import com.grappbox.grappbox.model.WhiteboardModel;
import com.grappbox.grappbox.views.Whiteboard;

import org.json.JSONArray;
import org.json.JSONException;

import java.util.List;

/**
 * A placeholder fragment containing a simple view.
 */
public class WhiteboardDrawingActivityFragment extends Fragment implements WhiteboardDrawingActivity.ResultDispatcher {

    private Whiteboard mDrawingArea;

    public WhiteboardDrawingActivityFragment() {
    }

    @Override
    public void onActivityCreated(Bundle savedInstanceState) {
        super.onActivityCreated(savedInstanceState);
        if (getActivity() instanceof WhiteboardDrawingActivity){
            ((WhiteboardDrawingActivity) getActivity()).registerObserver(this);
        }
    }

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        View v = inflater.inflate(R.layout.fragment_whiteboard_drawing, container, false);
        mDrawingArea = (Whiteboard) v.findViewById(R.id.whiteboard);
        return v;
    }


    @Override
    public void onOpen(JSONArray objects) {
        try {
            mDrawingArea.clear(objects == null);
            mDrawingArea.feed(objects);
        } catch (JSONException e) {
            e.printStackTrace();
        }
    }
}
