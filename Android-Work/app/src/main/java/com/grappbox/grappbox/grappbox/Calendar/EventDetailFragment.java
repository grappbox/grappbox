package com.grappbox.grappbox.grappbox.Calendar;

import android.content.ContentValues;
import android.os.AsyncTask;
import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import com.grappbox.grappbox.grappbox.Model.APIConnectAdapter;
import com.grappbox.grappbox.grappbox.Model.SessionAdapter;
import com.grappbox.grappbox.grappbox.R;

import java.io.IOException;
import java.util.List;

/**
 * Created by tan_f on 21/01/2016.
 */
public class EventDetailFragment extends Fragment {

    private View _rootView;
    private int _idEvent;

    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        _rootView = inflater.inflate(R.layout.fragment_event_details, container, false);
        _idEvent = getArguments().getInt("idEvent");

        APIRequestEvent event = new APIRequestEvent();
        event.execute();
        return _rootView;
    }

    public class APIRequestEvent extends AsyncTask<String, Void, List<ContentValues>> {

        @Override
        protected void onPostExecute(List<ContentValues> result)
        {
            super.onPostExecute(result);
        }

        @Override
        protected List<ContentValues> doInBackground(String ... param)
        {
            String resultAPI;
            Integer APIResponse;
            List<ContentValues> listResult = null;

            try {
                String token = SessionAdapter.getInstance().getToken();
                APIConnectAdapter.getInstance().startConnection("event/getevent/" + token + "/" + String.valueOf(_idEvent));
                APIConnectAdapter.getInstance().setRequestConnection("GET");

                APIResponse = APIConnectAdapter.getInstance().getResponseCode();
                Log.v("Response API :", APIResponse.toString());
                if (APIResponse == 200) {
/*                    resultAPI = APIConnectAdapter.getInstance().getInputSream();
                    listResult = APIConnectAdapter.getInstance().getMonthPlanning(resultAPI);*/
                } else {
                    return null;
                }

            } catch (IOException e){
                Log.e("APIConnection", "Error ", e);
                return null;
            } /*catch (JSONException j){
                Log.e("APIConnection", "Error ", j);
                return null;
            }*/finally {
                APIConnectAdapter.getInstance().closeConnection();
            }
            return listResult;
        }

    }
}
