package com.grappbox.grappbox;

import android.app.AlertDialog;
import android.app.Fragment;
import android.content.DialogInterface;
import android.content.Intent;
import android.os.Bundle;
import android.support.design.widget.BottomSheetDialog;
import android.text.Layout;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.Menu;
import android.view.MenuInflater;
import android.view.MenuItem;
import android.view.View;
import android.view.ViewGroup;
import android.widget.Switch;
import android.widget.TextView;

import com.grappbox.grappbox.model.WhiteboardModel;
import com.grappbox.grappbox.sync.GrappboxWhiteboardJIT;
import com.grappbox.grappbox.views.Whiteboard;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.util.List;

/**
 * A placeholder fragment containing a simple view.
 */
public class WhiteboardDrawingActivityFragment extends Fragment implements WhiteboardDrawingActivity.ResultDispatcher, Whiteboard.Callbacks {

    public static Whiteboard mDrawingArea = null;

    private View mBottomSheetView = null;
    private View mDialogShapeGenericSettings = null;
    private View mDialogTextGenericSettings = null;

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
    public void onDestroyView() {
        super.onDestroyView();
        mDrawingArea = null;
    }

    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setHasOptionsMenu(true);
    }

    @Override
    public void onCreateOptionsMenu(Menu menu, MenuInflater inflater) {
        super.onCreateOptionsMenu(menu, inflater);
        Log.e("TEST", "oncreateoptionsmenu");
    }

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        View v = inflater.inflate(R.layout.fragment_whiteboard_drawing, container, false);
        mDrawingArea = (Whiteboard) v.findViewById(R.id.whiteboard);
        mDrawingArea.registerListener(this);
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

    private void generateGenericShapeSettings(final Whiteboard.Tool tool, boolean isLine){
        AlertDialog.Builder builder = new AlertDialog.Builder(getActivity());
        builder.setView(mDialogShapeGenericSettings);
        builder.setNegativeButton(R.string.negative_response, new DialogInterface.OnClickListener() {
            @Override
            public void onClick(DialogInterface dialog, int which) {
                dialog.cancel();
            }
        });
        if (isLine){
            mDialogShapeGenericSettings.findViewById(R.id.input_background_color).setVisibility(View.GONE);
        } else {
            mDialogShapeGenericSettings.findViewById(R.id.input_background_color).setVisibility(View.VISIBLE);
        }
        builder.setPositiveButton(R.string.positive_response, new DialogInterface.OnClickListener() {
            @Override
            public void onClick(DialogInterface dialog, int which) {
                String lineStr = ((TextView) mDialogShapeGenericSettings.findViewById(R.id.input_line_weight)).getText().toString();
                String background = ((TextView) mDialogShapeGenericSettings.findViewById(R.id.input_background_color)).getText().toString();
                String stroke = ((TextView) mDialogShapeGenericSettings.findViewById(R.id.input_stroke_color)).getText().toString();
                int lineWeight = lineStr.isEmpty() ? Integer.MAX_VALUE : Integer.parseInt(((TextView) mDialogShapeGenericSettings.findViewById(R.id.input_line_weight)).getText().toString());
                mDrawingArea.setCurrentShapeSettings(background, stroke, lineWeight, tool);
                dialog.dismiss();
            }
        });
        builder.show();
    }

    private void generateTextSettings(){
        AlertDialog.Builder builder = new AlertDialog.Builder(getActivity());
        builder.setView(mDialogTextGenericSettings);
        builder.setNegativeButton(R.string.negative_response, new DialogInterface.OnClickListener() {
            @Override
            public void onClick(DialogInterface dialog, int which) {
                dialog.cancel();
            }
        });
        builder.setPositiveButton(R.string.positive_response, new DialogInterface.OnClickListener() {
            @Override
            public void onClick(DialogInterface dialog, int which) {
                if (((TextView) mDialogTextGenericSettings.findViewById(R.id.input_text_size)).getText().toString().isEmpty())
                    return;
                String color = ((TextView) mDialogTextGenericSettings.findViewById(R.id.input_color)).getText().toString();
                String text = ((TextView) mDialogTextGenericSettings.findViewById(R.id.input_text)).getText().toString();
                int textSize = Integer.parseInt(((TextView) mDialogTextGenericSettings.findViewById(R.id.input_text_size)).getText().toString());
                boolean italic = ((Switch) mDialogTextGenericSettings.findViewById(R.id.italic)).isChecked();
                boolean bold = ((Switch) mDialogTextGenericSettings.findViewById(R.id.bold)).isChecked();
                mDrawingArea.setCurrentTextSettings(color, textSize, text, italic, bold);
                dialog.dismiss();
            }
        });
        builder.show();
    }

    public void editTool(){
        final BottomSheetDialog dialog = new BottomSheetDialog(getActivity());
        LayoutInflater inflater = getActivity().getLayoutInflater();
        mBottomSheetView = inflater.inflate(R.layout.whiteboard_bottomsheet_tools, null, false);
        mDialogShapeGenericSettings = inflater.inflate(R.layout.dialog_shape_settings, null, false);
        mDialogTextGenericSettings = inflater.inflate(R.layout.dialog_text_settings, null, false);
        dialog.setContentView(mBottomSheetView);
        mBottomSheetView.findViewById(R.id.tool_move).setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                mDrawingArea.setTool(Whiteboard.Tool.E_MOVE);
                dialog.dismiss();
            }
        });

        mBottomSheetView.findViewById(R.id.tool_erase).setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                mDrawingArea.setTool(Whiteboard.Tool.E_ERASER);
                dialog.dismiss();
            }
        });

        mBottomSheetView.findViewById(R.id.tool_handwriting).setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                generateGenericShapeSettings(Whiteboard.Tool.E_HANDWRITE, true);
                dialog.dismiss();
            }
        });

        mBottomSheetView.findViewById(R.id.tool_line).setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                generateGenericShapeSettings(Whiteboard.Tool.E_LINE, true);
                dialog.dismiss();
            }
        });

        mBottomSheetView.findViewById(R.id.tool_rectangle).setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                generateGenericShapeSettings(Whiteboard.Tool.E_RECTANGLE, false);
                dialog.dismiss();
            }
        });

        mBottomSheetView.findViewById(R.id.tool_ellipsis).setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                generateGenericShapeSettings(Whiteboard.Tool.E_ELLIPSIS, false);
                dialog.dismiss();
            }
        });

        mBottomSheetView.findViewById(R.id.tool_text).setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                generateTextSettings();
                dialog.dismiss();
            }
        });

        mBottomSheetView.findViewById(R.id.tool_diamond).setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                generateGenericShapeSettings(Whiteboard.Tool.E_DIAMOND, false);
                dialog.dismiss();
            }
        });


        dialog.show();
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        Log.e("TEST", "Item menu selected");
        switch (item.getItemId()){
            case R.id.nav_change_tool:
                editTool();
                return true;
        }
        return false;
    }

    @Override
    public void onNewObject(JSONObject object) {
        Intent push = new Intent(getActivity(), GrappboxWhiteboardJIT.class);

        push.putExtra(GrappboxWhiteboardJIT.EXTRA_WHITEBOARD_ID, getActivity().getIntent().getStringExtra(WhiteboardDrawingActivity.EXTRA_WHITEBOARD_ID));
        push.putExtra(GrappboxWhiteboardJIT.EXTRA_JSON, object.toString());
        push.setAction(GrappboxWhiteboardJIT.ACTION_PUSH);
        getActivity().startService(push);
    }

    @Override
    public void onDeleteArea(JSONObject object) {
        Intent push = new Intent(getActivity(), GrappboxWhiteboardJIT.class);

        push.putExtra(GrappboxWhiteboardJIT.EXTRA_WHITEBOARD_ID, getActivity().getIntent().getStringExtra(WhiteboardDrawingActivity.EXTRA_WHITEBOARD_ID));
        push.putExtra(GrappboxWhiteboardJIT.EXTRA_JSON, object.toString());
        push.setAction(GrappboxWhiteboardJIT.ACTION_DELETE_OBJECT);
        getActivity().startService(push);
    }
}
