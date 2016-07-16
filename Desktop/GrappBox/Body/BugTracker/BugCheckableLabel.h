#ifndef BUGCHECKABLELABEL_H
#define BUGCHECKABLELABEL_H

#include <QWidget>
#include <QHBoxLayout>
#include <QLabel>
#include <QCheckBox>

class BugCheckableLabel : public QWidget
{
    Q_OBJECT
public:
    explicit        BugCheckableLabel(const int id, const QString &name, const bool checked, QWidget *parent = 0);
    const int       GetId() const;
    const QString   GetName() const;
    const bool      IsChecked() const;
    void            SetChecked(bool checked);

signals:
    void            OnCheckChanged(bool, int, QString);

public slots:
    void            TriggerCheckChange(bool checked);

private:
    QHBoxLayout     *_mainLayout;
    QCheckBox       *_checked;
    QLabel          *_name;
    int             _id;
};

#endif // BUGCHECKABLELABEL_H
