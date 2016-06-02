package com.grappbox.grappbox.grappbox.Model;

import android.graphics.drawable.Drawable;
import android.support.v4.app.Fragment;
import android.support.v4.content.ContextCompat;
import android.support.v4.graphics.drawable.DrawableCompat;
import android.view.View;
import android.widget.ProgressBar;

import com.grappbox.grappbox.grappbox.R;

import java.util.ArrayList;

/**
 * Created by wieser_m on 01/06/2016.
 */

public class LoadingFragment extends Fragment {
    private View[] _hiddenInLoading;
    private ProgressBar _loader;

    public void startLoading(View rootView,int progressID,  View... hide)
    {
        _hiddenInLoading = hide;
        for (View v : _hiddenInLoading)
            v.setVisibility(View.GONE);
        _loader = (ProgressBar)rootView.findViewById(progressID);
        _loader.setVisibility(View.VISIBLE);
        Drawable ld = _loader.getIndeterminateDrawable();
        DrawableCompat.setTint(ld, ContextCompat.getColor(getContext(), R.color.colorGrappboxRed));
        _loader.setIndeterminateDrawable(ld);
    }

    public void startLoading(View rootView, int progressID, int... hideIDs)
    {
        ArrayList<View> toHide = new ArrayList<>();

        for (int id : hideIDs)
            toHide.add(rootView.findViewById(id));
        Object[] toConvert = toHide.toArray();
        _hiddenInLoading = new View[toHide.size()];
        int i = 0;
        for (Object obj : toConvert)
            _hiddenInLoading[i++] = (View) obj;
        for (View v : _hiddenInLoading)
            v.setVisibility(View.GONE);
        _loader = (ProgressBar)rootView.findViewById(progressID);
        _loader.setVisibility(View.VISIBLE);
        Drawable ld = _loader.getIndeterminateDrawable();
        DrawableCompat.setTint(ld, ContextCompat.getColor(getContext(), R.color.colorGrappboxRed));
        _loader.setIndeterminateDrawable(ld);
    }

    public void endLoading()
    {
        _loader.setVisibility(View.GONE);
        for (View v : _hiddenInLoading)
            v.setVisibility(View.VISIBLE);
    }
}
