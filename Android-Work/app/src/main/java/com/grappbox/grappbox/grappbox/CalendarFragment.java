package com.grappbox.grappbox.grappbox;

import android.annotation.SuppressLint;
import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.support.v4.app.FragmentTransaction;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.Button;
import android.widget.TextView;
import android.widget.Toast;

import com.roomorama.caldroid.CaldroidFragment;
import com.roomorama.caldroid.CaldroidListener;

import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Calendar;
import java.util.Date;

@SuppressLint("SimpleDateFormat")
public class CalendarFragment extends Fragment {

    private boolean undo = false;
    private CaldroidFragment caldroidFragment;
    private CaldroidFragment caldroidDialogFragment;

    private void setCustomResourceForDates()
    {
        Calendar cal = Calendar.getInstance();

        cal.add(Calendar.DATE, -7);
        Date blueDate = cal.getTime();

        cal.add(Calendar.DATE, 7);
        Date greenDate = cal.getTime();
        if (caldroidFragment != null)
        {
            caldroidFragment.setBackgroundResourceForDate(R.color.blue_calendar, blueDate);
            caldroidFragment.setBackgroundResourceForDate(R.color.green_calendar, greenDate);
            caldroidFragment.setTextColorForDate(R.color.white_calendar, blueDate);
            caldroidFragment.setTextColorForDate(R.color.white_calendar, greenDate);
        }
    }

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        final View rootView = inflater.inflate(R.layout.fragment_calendar, container, false);

        final SimpleDateFormat format = new SimpleDateFormat("yyyy MMM dd");

        caldroidFragment = new CaldroidFragment();
        if (savedInstanceState != null) {
            caldroidFragment.restoreStatesFromKey(savedInstanceState, "CALDROID_SAVED_STATE");
        } else {
            Bundle args = new Bundle();
            Calendar calendar = Calendar.getInstance();
            args.putInt(CaldroidFragment.MONTH, calendar.get(Calendar.MONTH) + 1);
            args.putInt(CaldroidFragment.YEAR, calendar.get(Calendar.YEAR));
            args.putBoolean(CaldroidFragment.ENABLE_SWIPE, true);
            args.putBoolean(CaldroidFragment.SIX_WEEKS_IN_CALENDAR, true);
            caldroidFragment.setArguments(args);
        }

        FragmentTransaction t = getFragmentManager().beginTransaction();
        t.replace(R.id.calendar1, caldroidFragment);
        t.commit();

        setCustomResourceForDates();

        final CaldroidListener listener = new CaldroidListener() {
            @Override
            public void onSelectDate(Date date, View view) {
                Toast.makeText(rootView.getContext(), format.format(date), Toast.LENGTH_SHORT).show();
            }

            @Override
            public void onChangeMonth(int month, int year) {
                String text = "month: " + month + " year: " + year;
                Toast.makeText(rootView.getContext(), text,
                        Toast.LENGTH_SHORT).show();
            }

            @Override
            public void onLongClickDate(Date date, View view) {
                Toast.makeText(rootView.getContext(),
                        "Long click " + format.format(date),
                        Toast.LENGTH_SHORT).show();
            }

            @Override
            public void onCaldroidViewCreated() {
                if (caldroidFragment.getLeftArrowButton() != null) {
                    Toast.makeText(rootView.getContext(),
                            "Caldroid view is created", Toast.LENGTH_SHORT)
                            .show();
                }
            }
        };


        caldroidFragment.setCaldroidListener(listener);

        final TextView textView = (TextView) rootView.findViewById(R.id.textview);

        final Button customizeButton = (Button) rootView.findViewById(R.id.customize_button);

        // Customize the calendar
        customizeButton.setOnClickListener(new View.OnClickListener() {

            @Override
            public void onClick(View v) {
                if (undo) {
                    customizeButton.setText(getString(R.string.customize));
                    textView.setText("");

                    // Reset calendar
                    caldroidFragment.clearDisableDates();
                    caldroidFragment.clearSelectedDates();
                    caldroidFragment.setMinDate(null);
                    caldroidFragment.setMaxDate(null);
                    caldroidFragment.setShowNavigationArrows(true);
                    caldroidFragment.setEnableSwipe(true);
                    caldroidFragment.refreshView();
                    undo = false;
                    return;
                }

                // Else
                undo = true;
                customizeButton.setText(getString(R.string.undo));
                Calendar cal = Calendar.getInstance();

                // Min date is last 7 days
                cal.add(Calendar.DATE, -7);
                Date minDate = cal.getTime();

                // Max date is next 7 days
                cal = Calendar.getInstance();
                cal.add(Calendar.DATE, 14);
                Date maxDate = cal.getTime();

                // Set selected dates
                // From Date
                cal = Calendar.getInstance();
                cal.add(Calendar.DATE, 2);
                Date fromDate = cal.getTime();

                // To Date
                cal = Calendar.getInstance();
                cal.add(Calendar.DATE, 3);
                Date toDate = cal.getTime();

                // Set disabled dates
                ArrayList<Date> disabledDates = new ArrayList<Date>();
                for (int i = 5; i < 8; i++) {
                    cal = Calendar.getInstance();
                    cal.add(Calendar.DATE, i);
                    disabledDates.add(cal.getTime());
                }

                // Customize
                caldroidFragment.setMinDate(minDate);
                caldroidFragment.setMaxDate(maxDate);
                caldroidFragment.setDisableDates(disabledDates);
                caldroidFragment.setSelectedDates(fromDate, toDate);
                caldroidFragment.setShowNavigationArrows(false);
                caldroidFragment.setEnableSwipe(false);

                caldroidFragment.refreshView();

                // Move to date
                // cal = Calendar.getInstance();
                // cal.add(Calendar.MONTH, 12);
                // caldroidFragment.moveToDate(cal.getTime());

                String text = "Today: " + format.format(new Date()) + "\n";
                text += "Min Date: " + format.format(minDate) + "\n";
                text += "Max Date: " + format.format(maxDate) + "\n";
                text += "Select From Date: " + format.format(fromDate)
                        + "\n";
                text += "Select To Date: " + format.format(toDate) + "\n";
                for (Date date : disabledDates) {
                    text += "Disabled Date: " + format.format(date) + "\n";
                }

                textView.setText(text);
            }
        });

        Button showDialogButton = (Button) rootView.findViewById(R.id.show_dialog_button);

        final Bundle state = savedInstanceState;
        showDialogButton.setOnClickListener(new View.OnClickListener() {

            @Override
            public void onClick(View v) {
                // Setup caldroid to use as dialog
                caldroidDialogFragment = new CaldroidFragment();
                caldroidDialogFragment.setCaldroidListener(listener);

                // If activity is recovered from rotation
                final String dialogTag = "CALDROID_DIALOG_FRAGMENT";
                if (state != null) {
                    caldroidDialogFragment.restoreDialogStatesFromKey(
                            getFragmentManager(), state,
                            "DIALOG_CALDROID_SAVED_STATE", dialogTag);
                    Bundle args = caldroidDialogFragment.getArguments();
                    if (args == null) {
                        args = new Bundle();
                        caldroidDialogFragment.setArguments(args);
                    }
                } else {
                    // Setup arguments
                    Bundle bundle = new Bundle();
                    // Setup dialogTitle
                    caldroidDialogFragment.setArguments(bundle);
                }

                caldroidDialogFragment.show(getFragmentManager(),
                        dialogTag);
            }
        });
        return rootView;
    }


}
