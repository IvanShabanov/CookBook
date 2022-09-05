<?

/**
 * ����� ��������
 * @author runcore
 */
function num2str($inn, $stripkop = false)
{
	$nol = '����';
	$str[100] = array('', '���', '������', '������', '���������', '�������', '��������', '�������', '���������', '���������');
	$str[11] = array('', '������', '�����������', '����������', '����������', '������������', '����������', '�����������', '����������', '������������', '������������', '��������');
	$str[10] = array('', '������', '��������', '��������', '�����', '���������', '����������', '���������', '�����������', '���������');
	$sex = array(
		array('', '����', '���', '���', '������', '����', '�����', '����', '������', '������'), // m
		array('', '����', '���', '���', '������', '����', '�����', '����', '������', '������') // f
	);
	$forms = array(
		array('�������', '�������', '������', 1), // 10^-2
		array('�����', '�����', '������',  0), // 10^ 0
		array('������', '������', '�����', 1), // 10^ 3
		array('�������', '��������', '���������',  0), // 10^ 6
		array('��������', '���������', '����������',  0), // 10^ 9
		array('��������', '���������', '����������',  0), // 10^12
	);
	$out = $tmp = array();
	// �������!
	$tmp = explode('.', str_replace(',', '.', $inn));
	$rub = number_format($tmp[0], 0, '', '-');
	if ($rub == 0) $out[] = $nol;
	// ������������ ������
	$kop = isset($tmp[1]) ? substr(str_pad($tmp[1], 2, '0', STR_PAD_RIGHT), 0, 2) : '00';
	$segments = explode('-', $rub);
	$offset = sizeof($segments);
	if ((int)$rub == 0) { // ���� 0 ������
		$o[] = $nol;
		$o[] = morph(0, $forms[1][0], $forms[1][1], $forms[1][2]);
	} else {
		foreach ($segments as $k => $lev) {
			$sexi = (int) $forms[$offset][3]; // ���������� ���
			$ri = (int) $lev; // ������� �������
			if ($ri == 0 && $offset > 1) { // ���� �������==0 & �� ��������� �������(��� Units)
				$offset--;
				continue;
			}
			// ������������
			$ri = str_pad($ri, 3, '0', STR_PAD_LEFT);
			// �������� ������� ��� �������
			$r1 = (int)substr($ri, 0, 1); //������ �����
			$r2 = (int)substr($ri, 1, 1); //������
			$r3 = (int)substr($ri, 2, 1); //������
			$r22 = (int)$r2 . $r3; //������ � ������
			// ���������� �������
			if ($ri > 99) $o[] = $str[100][$r1]; // �����
			if ($r22 > 20) { // >20
				$o[] = $str[10][$r2];
				$o[] = $sex[$sexi][$r3];
			} else { // <=20
				if ($r22 > 9) $o[] = $str[11][$r22 - 9]; // 10-20
				elseif ($r22 > 0) $o[] = $sex[$sexi][$r3]; // 1-9
			}
			// �����
			$o[] = morph($ri, $forms[$offset][0], $forms[$offset][1], $forms[$offset][2]);
			$offset--;
		}
	}
	// �������
	if (!$stripkop) {
		$o[] = $kop;
		$o[] = morph($kop, $forms[0][0], $forms[0][1], $forms[0][2]);
	}
	return preg_replace("/\s{2,}/", ' ', implode(' ', $o));
}

/**
 * �������� ����������
 */
function morph($n, $f1, $f2, $f5)
{
	$n = abs($n) % 100;
	$n1 = $n % 10;
	if ($n > 10 && $n < 20) return $f5;
	if ($n1 > 1 && $n1 < 5) return $f2;
	if ($n1 == 1) return $f1;
	return $f5;
}
