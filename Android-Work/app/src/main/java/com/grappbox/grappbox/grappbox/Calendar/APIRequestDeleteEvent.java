package com.grappbox.grappbox.grappbox.Calendar;

import android.app.Dialog;
import android.os.AsyncTask;
import android.util.Log;
import android.widget.Toast;

import com.grappbox.grappbox.grappbox.Model.APIConnectAdapter;
import com.grappbox.grappbox.grappbox.Model.SessionAdapter;
import com.grappbox.grappbox.grappbox.R;

import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;

/**
 * Created by tan_f on 05/02/2016.
 */
public class APIRequestDeleteEvent extends AsyncTask<String, Void, String> {

    private EventDetailFragment _context;
    private int _idEvent;
    private Dialog _dialog;

    APIRequestDeleteEvent(EventDetailFragment context, int idEvent, Dialog deleteDialog)
    {
        _context = context;
        _idEvent = idEvent;
        _dialog = deleteDialog;
    }

    @Override
    protected void onPostExecute(String result)
    {
        super.onPostExecute(result);
        if (result != null) {
            CharSequence text = "Event correctly delete";
            Toast.makeText(_context.getContext(), text, Toast.LENGTH_SHORT).show();
            _dialog.dismiss();
            AgendaFragment agendaFragment = new AgendaFragment();
            _context.getFragmentManager().beginTransaction().replace(R.id.content_frame, agendaFragment).commit();
        } else {
            CharSequence text = "An Error Occured.";
            Toast.makeText(_context.getContext(), text, Toast.LENGTH_SHORT).show();
        }
    }

    @Override
    protected String doInBackground(String ... param)
    {
        String resultAPI;
        Integer APIResponse;

        try {
            String token = SessionAdapter.getInstance().getUserData(SessionAdapter.KEY_TOKEN);
            APIConnectAdapter.getInstance().startConnection("event/delevent/" + token + "/" + _idEvent, "V0.2");
            APIConnectAdapter.getInstance().setRequestConnection("DELETE");


            APIResponse = APIConnectAdapter.getInstance().getResponseCode();
            Log.v("Response API :", APIResponse.toString());
            if (APIResponse == 200) {
                resultAPI = APIConnectAdapter.getInstance().getInputSream();
            } else {
                return null;
            }

        } catch (IOException e){
            Log.e("APIConnection", "Error ", e);
            return null;
        } finally {
            APIConnectAdapter.getInstance().closeConnection();
        }
        return resultAPI;
    }

}
