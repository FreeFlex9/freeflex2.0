export function validateCpf(value) {
  const cpf = value.replace(/\D/g, '')
  if (cpf.length !== 11) return false
  if (/^(\d)\1{10}$/.test(cpf)) return false

  let sum = 0
  for (let i = 0; i < 9; i++) sum += parseInt(cpf[i]) * (10 - i)
  let r = sum % 11
  if ((r < 2 ? 0 : 11 - r) !== parseInt(cpf[9])) return false

  sum = 0
  for (let i = 0; i < 10; i++) sum += parseInt(cpf[i]) * (11 - i)
  r = sum % 11
  return (r < 2 ? 0 : 11 - r) === parseInt(cpf[10])
}

export function validateCnpj(value) {
  const cnpj = value.replace(/\D/g, '')
  if (cnpj.length !== 14) return false
  if (/^(\d)\1{13}$/.test(cnpj)) return false

  const calc = (cnpj, weights) => {
    const sum = weights.reduce((acc, w, i) => acc + parseInt(cnpj[i]) * w, 0)
    const r = sum % 11
    return r < 2 ? 0 : 11 - r
  }

  const d1 = calc(cnpj, [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2])
  if (d1 !== parseInt(cnpj[12])) return false

  const d2 = calc(cnpj, [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2])
  return d2 === parseInt(cnpj[13])
}

export function validatePhone(value) {
  const phone = value.replace(/\D/g, '')
  if (phone.length < 10 || phone.length > 11) return false
  const ddd = parseInt(phone.substring(0, 2))
  if (ddd < 11 || ddd > 99) return false
  // Celular: 11 dígitos, 3º dígito deve ser 9
  if (phone.length === 11 && phone[2] !== '9') return false
  return true
}
